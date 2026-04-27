<?php

namespace Administration\Services;

use Administration\Dtos\ResetUserPasswordDto;
use Administration\Dtos\SetUserActiveDto;
use Administration\Dtos\UserCreateDto;
use Administration\Dtos\UserListQueryDto;
use Administration\Dtos\UserShowDto;
use Administration\Dtos\UserUpdateDto;
use Administration\Events\UserAvatarUpdatedEvent;
use Administration\Events\UserCreatedEvent;
use Administration\Events\UserDeletedEvent;
use Administration\Events\UserUpdatedEvent;
use Administration\Models\User;
use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use App\Mail\UserPasswordResetMail;
use App\Mail\UserWelcomeMail;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Tenant user management service — CRUD, avatar, active flag, password reset.
 * All data access goes through repositories; the acting user is passed explicitly as `$actor`.
 */
readonly class UserManagementService implements UserManagementServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository users repository
     * @param TokenRepositoryInterface $tokenRepository Passport token repository (session revocation)
     * @param AuthLogRepositoryInterface $authLogRepository auth log repository (audit entries)
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenRepositoryInterface $tokenRepository,
        private AuthLogRepositoryInterface $authLogRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function list(UserListQueryDto $dto): LengthAwarePaginator
    {
        $users = $this->userRepository->paginateByCompany(
            $dto->getCompanyId(),
            $dto->getPerPage(),
            $dto->toFilters(),
        );
        User::primeActivityCache($users->pluck('id')->all());
        return $users;
    }

    /**
     * @inheritDoc
     */
    public function show(UserShowDto $dto): User
    {
        return $this->userRepository->findOrFail($dto->getUserId(), ['roles:id,name', 'permissions:id,name']);
    }

    /**
     * @inheritDoc
     */
    public function create(UserCreateDto $dto, User $actor): User
    {
        $user = $this->userRepository->create($dto);

        setPermissionsTeamId($actor->company_id);

        $user->assignRole($dto->getRoleName());
        $user->load('roles:id,name');

        event(new UserCreatedEvent($user, $actor));

        // Welcome email with sign-in credentials. Same fail-safe pattern as resetPassword:
        // queue the mail (non-blocking) and swallow transport errors so a flaky SMTP doesn't
        // prevent the user from being created.
        try {
            Mail::to($user->email)->queue(new UserWelcomeMail(
                userName: $user->name,
                userEmail: $user->email,
                password: $dto->getPassword(),
                appName: (string) config('app.name', 'Vision'),
                appUrl: (string) config('app.url'),
            ));
        } catch (Throwable $e) {
            Log::warning('UserWelcomeMail dispatch failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function update(int $userId, UserUpdateDto $dto, User $actor): User
    {
        $user = $this->userRepository->findOrFail($userId);
        $user = $this->userRepository->update($user, $dto->toArray());

        // Roles live in the Spatie pivot, not on sec_users — UserUpdateDto::toArray() does not
        // expose them, so the repository update never touches role assignment. Sync explicitly
        // here when the DTO carries a role; null means "leave the existing role alone".
        if ($dto->getRoleName() !== null) {
            setPermissionsTeamId($user->company_id);
            $user->syncRoles($dto->getRoleName());
        }

        $user->load('roles:id,name');

        event(new UserUpdatedEvent($user, $actor));

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $userId, User $actor): bool
    {
        $user = $this->userRepository->findOrFail($userId);

        event(new UserDeletedEvent($user, $actor));

        return $this->userRepository->delete($user);
    }

    /**
     * @inheritDoc
     */
    public function updateAvatar(int $userId, UploadedFile $avatar, User $actor): User
    {
        $user = $this->userRepository->findOrFail($userId);
        $user = $this->userRepository->updateAvatar($user, $avatar);

        $user->load('roles:id,name');

        event(new UserAvatarUpdatedEvent($user, $actor));

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function setActive(SetUserActiveDto $dto, User $actor): User
    {
        $user = $this->userRepository->findOrFail($dto->getUserId());
        $user = $this->userRepository->update($user, ['is_active' => $dto->isActive()]);
        $user->load('roles:id,name');
        event(new UserUpdatedEvent($user, $actor));
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function resetPassword(ResetUserPasswordDto $dto, User $actor): string
    {
        $user = $this->userRepository->findOrFail($dto->getUserId());
        $plain = Str::random(12);

        $this->userRepository->update($user, ['password' => $plain]);
        $this->tokenRepository->revokeAllForUser($dto->getUserId());

        $this->authLogRepository->log(
            action: 'password_reset',
            model: User::class,
            userId: (int) $user->id,
            companyId: (int) $user->company_id,
            ip: $dto->getIp(),
            userAgent: $dto->getUserAgent(),
        );

        // Queue the email — the password is already rotated and sessions revoked, so the
        // admin's HTTP request returns instantly and the queue worker handles SMTP latency.
        // Mail driver is `log` until SMTP is wired up; the body lands in storage/logs/laravel.log
        // so ops can verify the template before flipping MAIL_MAILER.
        try {
            Mail::to($user->email)->queue(new UserPasswordResetMail(
                userName: $user->name,
                userEmail: $user->email,
                temporaryPassword: $plain,
                appName: (string) config('app.name', 'Vision'),
                appUrl: (string) config('app.url'),
            ));
        } catch (Throwable $e) {
            // Never let mail-dispatch hiccups bubble up to the admin clicking the button —
            // the password is already rotated. Just log so ops sees it.
            Log::warning('UserPasswordResetMail dispatch failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $plain;
    }
}
