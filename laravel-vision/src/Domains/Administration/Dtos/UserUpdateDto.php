<?php

namespace Administration\Dtos;

/**
 * DTO carrying data for updating an existing user.
 * All fields are optional - only the ones provided are changed.
 */
readonly class UserUpdateDto
{
    /**
     * Builds the DTO with the user update data.
     *
     * @param string|null $name new full name or null to leave unchanged
     * @param string|null $email new email address or null to leave unchanged
     * @param string|null $password new password or null to leave unchanged
     * @param string|null $roleName new role name or null to leave unchanged
     * @param bool|null $isActive new active flag or null to leave unchanged
     */
    public function __construct(
        private ?string $name = null,
        private ?string $email = null,
        private ?string $password = null,
        private ?string $roleName = null,
        private ?bool $isActive = null,
    ) {}

    /**
     * Returns the user's new full name (or null when unchanged).
     *
     * @return string|null new name or null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns the user's new email address (or null when unchanged).
     *
     * @return string|null new email or null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Returns the user's new password (or null when unchanged).
     *
     * @return string|null new password or null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Returns the new role name (or null when unchanged).
     *
     * @return string|null new role name or null
     */
    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    /**
     * Returns the new active status of the account (or null when unchanged).
     *
     * @return bool|null new active flag or null
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Converts the DTO to an array, omitting null fields.
     *
     * @return array<string, mixed> update data as a key-value array
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null);
    }
}
