<?php

namespace Administration\Repositories;

use Administration\Dtos\RoleDto;
use Administration\Repositories\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Role repository - concrete database operations on Spatie's Role model.
 * Note: Role is an external model so it doesn't use the BelongsToCompany trait nor CompanyScope -
 * the company restriction must be added manually via where('company_id', ...) in every query.
 */
class RoleRepository implements RoleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll(): Collection
    {
        return Role::where('company_id', getPermissionsTeamId())
            ->with('permissions:id,name')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $roleId): Role
    {
        return Role::where('company_id', getPermissionsTeamId())
            ->findOrFail($roleId);
    }

    /**
     * Creates a new role in the current company and assigns the permissions from the DTO.
     *
     * @param RoleDto $dto DTO with the role name and permission list
     * @return Role created role with permissions loaded
     */
    public function create(RoleDto $dto): Role
    {
        $role = Role::create([
            'name' => $dto->getName(),
            'guard_name' => 'api',
            'company_id' => getPermissionsTeamId(),
        ]);

        $role->syncPermissions($dto->getPermissions());

        return $role->load('permissions:id,name');
    }

    /**
     * Updates the role name and synchronizes the permissions list with the DTO.
     *
     * @param Role $role role to update
     * @param RoleDto $dto DTO with the new role data
     * @return Role updated role with permissions loaded
     */
    public function update(Role $role, RoleDto $dto): Role
    {
        $role->update(['name' => $dto->getName()]);
        $role->syncPermissions($dto->getPermissions());

        return $role->load('permissions:id,name');
    }

    /**
     * @inheritDoc
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }
}
