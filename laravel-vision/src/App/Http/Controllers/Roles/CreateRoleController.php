<?php

namespace App\Http\Controllers\Roles;

use Administration\Requests\RoleCreateRequest;
use Administration\Services\Interfaces\RoleServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /administration/roles — creates a new role.
 */
readonly class CreateRoleController
{
    /**
     * @param RoleServiceInterface $roleService role management service
     */
    public function __construct(private RoleServiceInterface $roleService)
    {
    }

    /**
     * @param RoleCreateRequest $request validated role creation request
     * @return JsonResponse created role (status 201)
     */
    public function __invoke(RoleCreateRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole($request->getDto());
        return response()->json(['data' => GetRolesController::serialize($role)], 201);
    }
}
