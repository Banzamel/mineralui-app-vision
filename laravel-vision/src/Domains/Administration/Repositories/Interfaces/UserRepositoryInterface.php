<?php

namespace Administration\Repositories\Interfaces;

use Administration\Models\User;
use Illuminate\Http\UploadedFile;
use Administration\Dtos\UserCreateDto;

/**
 * Contract for the user repository.
 * Defines read/write operations for user data in the database.
 */
interface UserRepositoryInterface
{
    /**
     * Finds a user by id or throws when none is found.
     *
     * @param int $userId user identifier
     * @param array $with list of relations to eager-load
     * @return User the resolved user
     */
    public function findOrFail(int $userId, array $with = []): User;

    /**
     * Persists a new user in the database.
     *
     * @param UserCreateDto $dto DTO with the new user's data
     * @return User created user
     */
    public function create(UserCreateDto $dto): User;

    /**
     * Updates the given user with the provided fields.
     *
     * @param User $user user to update
     * @param array $data fields to change
     * @return User freshly fetched user after the update
     */
    public function update(User $user, array $data): User;

    /**
     * Deletes the user from the database (soft delete).
     *
     * @param User $user user to delete
     * @return bool true when the operation succeeded
     */
    public function delete(User $user): bool;

    /**
     * Stores a new avatar file for the user and updates the path in the database.
     *
     * @param User $user user whose avatar is being changed
     * @param UploadedFile $avatar new avatar file
     * @return User user with the updated avatar path
     */
    public function updateAvatar(User $user, UploadedFile $avatar): User;

    /**
     * Counts users belonging to a company.
     *
     * @param int $companyId tenant id
     * @return int total user count
     */
    public function countByCompany(int $companyId): int;

    /**
     * Counts active users belonging to a company.
     *
     * @param int $companyId tenant id
     * @return int active user count
     */
    public function countActiveByCompany(int $companyId): int;

    /**
     * Returns ids of all users belonging to a company.
     *
     * @param int $companyId tenant id
     * @return array<int, int> list of user ids
     */
    public function idsByCompany(int $companyId): array;

    /**
     * Finds a user by id but only when it belongs to the given company, otherwise null.
     *
     * @param int $userId user id
     * @param int $companyId tenant id
     * @return User|null user when found in this tenant, otherwise null
     */
    public function findInCompany(int $userId, int $companyId): ?User;

    /**
     * Returns a paginated list of users belonging to a company with optional filters.
     *
     * Supported filters (all optional):
     *  - search (string) — matches name or email (LIKE)
     *  - role (string)   — role name assigned through Spatie
     *  - is_active (bool)
     *  - sort_by (string) — allowed columns: name, email, created_at
     *  - sort_order (asc|desc)
     *
     * @param int $companyId tenant id
     * @param int $perPage rows per page
     * @param array<string, mixed> $filters filter bag
     * @return \Illuminate\Pagination\LengthAwarePaginator paginated users
     */
    public function paginateByCompany(int $companyId, int $perPage, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator;
}
