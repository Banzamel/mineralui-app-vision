<?php

namespace Administration\Dtos;

/**
 * DTO carrying role data (name and list of permissions).
 * Used when creating and updating company roles.
 */
readonly class RoleDto
{
    /**
     * Builds the DTO from form data.
     *
     * @param string $name role name
     * @param array $permissions list of permission names assigned to the role
     */
    public function __construct(
        private string $name,
        private array $permissions,
    ) {}

    /**
     * Returns the role name.
     *
     * @return string role name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the list of permissions assigned to this role.
     *
     * @return array array of permission names
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Converts the DTO to an array ready for persistence.
     *
     * @return array<string, mixed> role data as a key-value array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'permissions' => $this->permissions,
        ];
    }
}
