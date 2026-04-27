<?php

namespace App\Http\Controllers\Roles;

use Illuminate\Http\JsonResponse;
use Administration\Services\Interfaces\RoleServiceInterface;

/**
 * Controller returning a list of all roles available in the system.
 */
readonly class GetRolesController
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
     * Returns a list of all user roles in the system.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with the role list
     */
    public function __invoke(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();

        return response()->json(['data' => $roles->map(fn ($role) => self::serialize($role))]);
    }

    /**
     * Maps a Spatie role model into the flat shape the frontend expects:
     * `{id, name, company_id, permissions: string[]}`. Without this, JSON serialization
     * leaves `permissions` as Permission objects (`{id, name, pivot}`), which the React
     * roles table can't render and React warns about duplicate `[object Object]` keys.
     *
     * @param \Spatie\Permission\Models\Role $role
     * @return array<string, mixed>
     */
    public static function serialize(\Spatie\Permission\Models\Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'company_id' => $role->company_id,
            'permissions' => $role->permissions->pluck('name')->all(),
        ];
    }
}
