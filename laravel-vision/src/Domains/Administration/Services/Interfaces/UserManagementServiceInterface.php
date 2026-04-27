<?php

namespace Administration\Services\Interfaces;

use Administration\Dtos\ResetUserPasswordDto;
use Administration\Dtos\SetUserActiveDto;
use Administration\Dtos\UserCreateDto;
use Administration\Dtos\UserListQueryDto;
use Administration\Dtos\UserShowDto;
use Administration\Dtos\UserUpdateDto;
use Administration\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Shared\Exceptions\ApiJsonException;

/**
 * Contract for the tenant user management service.
 * Mutating operations take a `$actor` parameter so the service never reads the global auth state.
 */
interface UserManagementServiceInterface
{
    /**
     * Returns a paginated list of users in the tenant, filtered/sorted per DTO.
     *
     * @param UserListQueryDto $dto listing parameters (tenant scope + filters + pagination)
     * @return LengthAwarePaginator paginated list of users
     */
    public function list(UserListQueryDto $dto): LengthAwarePaginator;

    /**
     * Retrieves a single user by id with roles and permissions eager-loaded.
     *
     * @param UserShowDto $dto user id wrapper
     * @return User resolved user with relations
     */
    public function show(UserShowDto $dto): User;

    /**
     * Creates a new user in the tenant of `$actor`, assigns the role and dispatches the creation event.
     *
     * @param UserCreateDto $dto new user form data
     * @param User $actor user performing the action (source of company_id and event actor)
     * @return User created user with roles loaded
     */
    public function create(UserCreateDto $dto, User $actor): User;

    /**
     * Updates the given user's data and dispatches the update event.
     *
     * @param int $userId target user id
     * @param UserUpdateDto $dto fields to change
     * @param User $actor user performing the action
     * @return User updated user with roles loaded
     * @throws ApiJsonException when the target cannot be resolved
     */
    public function update(int $userId, UserUpdateDto $dto, User $actor): User;

    /**
     * Deletes a user (soft delete) and dispatches the event.
     *
     * @param int $userId target user id
     * @param User $actor user performing the action
     * @return bool true when deletion succeeded
     */
    public function delete(int $userId, User $actor): bool;

    /**
     * Replaces the user's avatar and dispatches the avatar change event.
     *
     * @param int $userId target user id
     * @param UploadedFile $avatar new avatar file
     * @param User $actor user performing the action
     * @return User user with the updated avatar and roles
     */
    public function updateAvatar(int $userId, UploadedFile $avatar, User $actor): User;

    /**
     * Toggles the user's is_active flag and dispatches the update event.
     *
     * @param SetUserActiveDto $dto target user id and desired active flag
     * @param User $actor user performing the action
     * @return User updated user with roles loaded
     */
    public function setActive(SetUserActiveDto $dto, User $actor): User;

    /**
     * Resets the password to a random string, revokes all sessions and writes an audit log entry.
     *
     * @param ResetUserPasswordDto $dto target user id + caller IP/user-agent for the log
     * @param User $actor user performing the action
     * @return string plain temporary password to be delivered out-of-band
     */
    public function resetPassword(ResetUserPasswordDto $dto, User $actor): string;
}
