<?php

namespace Administration\Services\Interfaces;

/**
 * Contract for the service that returns available permissions.
 * Defines the API for the class delivering permissions grouped by module.
 */
interface PermissionServiceInterface
{
    /**
     * Returns all available permissions grouped by application module.
     *
     * @return array module => list of permissions
     */
    public function getAllPermissions(): array;
}
