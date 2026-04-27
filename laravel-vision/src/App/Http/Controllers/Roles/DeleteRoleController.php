<?php

namespace App\Http\Controllers\Roles;

use Illuminate\Http\JsonResponse;
use Administration\Services\Interfaces\RoleServiceInterface;

/**
 * Controller deleting a user role from the system.
 */
readonly class DeleteRoleController
{
    /**
     * Initializes the controller with the role management service.
     *
     * @param \Administration\Services\Interfaces\RoleServiceInterface $roleService Role management service
     */
    public function __construct(private RoleServiceInterface $roleService)
    {
    }

    /**
     * Deletes the role with the given identifier.
     *
     * @param int $id Identifier of the role to delete
     * @return \Illuminate\Http\JsonResponse Empty response with 204 status
     */
    public function __invoke(int $id): JsonResponse
    {
        $this->roleService->deleteRole($id);

        return response()->json(null, 204);
    }
}
