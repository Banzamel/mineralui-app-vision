<?php

namespace Administration\Services;

use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;

/**
 * Service returning the list of available permissions grouped by module.
 * Reads the module configuration and pairs entries with rows from the permissions table.
 */
readonly class PermissionService implements Interfaces\PermissionServiceInterface
{
    /**
     * Returns permission names grouped by module — `Record<string, string[]>` shape the frontend
     * expects (`PermissionsByModule` in features/users/types.ts). Each module entry is the
     * intersection of `config('permission.modules')` and rows actually present in the database.
     *
     * @return array<string, list<string>> module => list of permission names
     */
    public function getAllPermissions(): array
    {
        $modules = Config::get('permission.modules', []);

        return array_map(function ($permissions) {
            return Permission::whereIn('name', $permissions)
                ->pluck('name')
                ->all();
        }, $modules);
    }
}
