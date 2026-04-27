<?php

namespace App\Http\Controllers\Roles;

use Administration\Requests\RoleUpdateRequest;
use Administration\Services\Interfaces\RoleServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * PUT /administration/roles/{role} — updates an existing role.
 */
readonly class UpdateRoleController
{
    /**
     * @param RoleServiceInterface $roleService role management service
     */
    public function __construct(private RoleServiceInterface $roleService)
    {
    }

    /**
     * @param RoleUpdateRequest $request validated role update request
     * @param int $id route role id
     * @return JsonResponse updated role
     */
    public function __invoke(RoleUpdateRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->updateRole($id, $request->getDto());
        return response()->json(['data' => GetRolesController::serialize($role)]);
    }
}
