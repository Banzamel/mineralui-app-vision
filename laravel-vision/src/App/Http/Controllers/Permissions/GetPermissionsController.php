<?php

namespace App\Http\Controllers\Permissions;

/**
 * Controller returning a list of all permissions available in the system.
 */
readonly class GetPermissionsController
{
    /**
     * Initializes the controller with the permission service.
     *
     * @param \Administration\Services\Interfaces\PermissionServiceInterface $permissionService Permission management service
     */
    public function __construct(private readonly \Administration\Services\Interfaces\PermissionServiceInterface $permissionService)
    {
    }

    /**
     * Returns a list of all permissions available in the system.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with the permission list
     */
    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        $permissions = $this->permissionService->getAllPermissions();

        return response()->json(['data' => $permissions]);
    }
}
