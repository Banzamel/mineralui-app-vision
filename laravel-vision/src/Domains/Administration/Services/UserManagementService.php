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
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function update(int $userId, UserUpdateDto $dto, User $actor): User
    {
        $user = $this->userRepository->findOrFail($userId);
        $user = $this->userRepository->update($user, $dto->toArray());

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

        return $plain;
    }
}
