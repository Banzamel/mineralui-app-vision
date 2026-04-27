<?php

namespace Administration\Services\Interfaces;

use Administration\Dtos\RoleDto;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for the company role management service.
 * Defines CRUD operations on roles together with their permissions.
 */
interface RoleServiceInterface
{
    /**
     * Returns all roles of the current company together with their permissions.
     *
     * @return Collection collection of Role objects
     */
    public function getAllRoles(): Collection;

    /**
     * Creates a new role in the company based on the DTO data.
     *
     * @param RoleDto $dto DTO with the role name and permission list
     * @return Role created Role object
     */
    public function createRole(RoleDto $dto): Role;

    /**
     * Updates an existing role in the company (name and permissions).
     *
     * @param int $id identifier of the role to update
     * @param RoleDto $dto DTO with the new name and permission list
     * @return Role updated Role object
     */
    public function updateRole(int $id, RoleDto $dto): Role;

    /**
     * Removes a role from the company.
     *
     * @param int $id identifier of the role to remove
     * @return bool true when deleted successfully
     */
    public function deleteRole(int $id): bool;
}
