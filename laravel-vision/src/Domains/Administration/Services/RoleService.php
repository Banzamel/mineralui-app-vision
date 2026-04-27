<?php

namespace Administration\Services;

use Administration\Dtos\RoleDto;
use Administration\Events\RoleCreatedEvent;
use Administration\Events\RoleUpdatedEvent;
use Administration\Events\RoleDeletedEvent;
use Administration\Repositories\Interfaces\RoleRepositoryInterface;
use Administration\Services\Interfaces\RoleServiceInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Role management service for the company.
 * Handles creation, updates and deletion of roles plus dispatching of domain events.
 */
readonly class RoleService implements RoleServiceInterface
{
    /**
     * Injects the role repository required for database operations.
     *
     * @param RoleRepositoryInterface $roleRepository role read/write repository
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @inheritDoc
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->findAll();
    }

    /**
     * Creates a new role in the company with the chosen permissions and dispatches the creation event.
     *
     * @param RoleDto $dto DTO with the role name and permission list
     * @return Role newly created role
     */
    public function createRole(RoleDto $dto): Role
    {
        $role = $this->roleRepository->create($dto);

        event(new RoleCreatedEvent($role, auth()->user()));

        return $role;
    }

    /**
     * Updates a role in the company (name and permissions) and dispatches the update event.
     *
     * @param int $id identifier of the role to update
     * @param RoleDto $dto DTO with the new name and permission list
     * @return Role updated role
     */
    public function updateRole(int $id, RoleDto $dto): Role
    {
        $role = $this->roleRepository->findOrFail($id);
        $role = $this->roleRepository->update($role, $dto);

        event(new RoleUpdatedEvent($role, auth()->user()));

        return $role;
    }

    /**
     * Removes a role from the company and dispatches the deletion event.
     *
     * @param int $id identifier of the role to delete
     * @return bool true when deleted successfully
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->roleRepository->findOrFail($id);

        event(new RoleDeletedEvent($role, auth()->user()));

        return $this->roleRepository->delete($role);
    }
}
