<?php

namespace Administration\Repositories;

use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Administration\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Administration\Dtos\UserCreateDto;

/**
 * User repository - concrete database operations on the User model.
 * Handles persistence, retrieval, updates, deletion and avatar uploads.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOrFail(int $userId, array $with = []): User
    {
        return User::query()
            ->when(!empty($with), fn($q) => $q->with($with))
            ->findOrFail($userId);
    }

    /**
     * Creates a new user in the database and automatically marks the email as verified.
     *
     * @param UserCreateDto $dto DTO with the new user's data
     * @return User newly created user
     */
    public function create(UserCreateDto $dto): User
    {
        return User::create([
            'email_verified_at' => now(),
            ...$dto->toArray(),
        ]);
    }

    /**
     * Updates the user's fields and returns the freshly loaded object from the database.
     *
     * @param User $user user to update
     * @param array $data fields to change
     * @return User updated user re-fetched from the database
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Stores the new avatar file in storage, removes the previous one and updates the path in the database.
     *
     * @param User $user user whose avatar is being changed
     * @param UploadedFile $avatar new avatar file
     * @return User user refreshed after the new avatar path is saved
     */
    public function updateAvatar(User $user, UploadedFile $avatar): User
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $avatar->storePublicly("avatars/{$user->company_id}", 'public');
        $user->update(['avatar_path' => $path]);

        return $user->fresh();
    }

    /**
     * @inheritDoc
     */
    public function countByCompany(int $companyId): int
    {
        return (int) User::query()->where('company_id', $companyId)->count();
    }

    /**
     * @inheritDoc
     */
    public function countActiveByCompany(int $companyId): int
    {
        return (int) User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function idsByCompany(int $companyId): array
    {
        return User::query()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->all();
    }

    /**
     * @inheritDoc
     */
    public function findInCompany(int $userId, int $companyId): ?User
    {
        return User::query()
            ->where('id', $userId)
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function paginateByCompany(int $companyId, int $perPage, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Eager-load roles + userScopes — without it the frontend table receives
        // u.roles/u.scopes = undefined and crashes when iterating / on .length.
        $query = User::query()
            ->with('roles:id,name')
            ->with('userScopes:user_id,type,scope_id')
            ->where('company_id', $companyId);

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('email', 'like', $search);
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $sortBy = $filters['sort_by'] ?? null;
        $sortOrder = strtolower((string) ($filters['sort_order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        if (in_array($sortBy, ['name', 'email', 'created_at'], true)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }
}
