<?php

namespace Administration\Repositories\Interfaces;

use Administration\Dtos\RoleDto;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for the role repository.
 * Defines read/write operations for role data together with their permissions.
 */
interface RoleRepositoryInterface
{
    /**
     * Returns all roles of the current company together with their permissions.
     *
     * @return Collection collection of Role objects
     */
    public function findAll(): Collection;

    /**
     * Finds a role by id within the current company or throws 404.
     *
     * @param int $roleId role identifier
     * @return Role the resolved role
     */
    public function findOrFail(int $roleId): Role;

    /**
     * Creates a new role with permissions in the current company.
     *
     * @param RoleDto $dto DTO with the role name and permission list
     * @return Role created role with permissions loaded
     */
    public function create(RoleDto $dto): Role;

    /**
     * Updates the role (name and permission list) according to the DTO data.
     *
     * @param Role $role existing role to update
     * @param RoleDto $dto DTO with the new role data
     * @return Role updated role with permissions loaded
     */
    public function update(Role $role, RoleDto $dto): Role;

    /**
     * Removes the role from the database.
     *
     * @param Role $role role to delete
     * @return bool true when the operation succeeded
     */
    public function delete(Role $role): bool;
}
