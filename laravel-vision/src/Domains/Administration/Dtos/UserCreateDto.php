<?php

namespace Administration\Dtos;

/**
 * DTO carrying the data required to create a new user.
 * Collects the full set of form fields and forwards them to the persistence layer.
 */
readonly class UserCreateDto
{
    /**
     * Builds the DTO with the new user's data.
     *
     * @param string $name user's first and last name
     * @param string $email user's email address
     * @param string $password plain-text password (hashed by the model)
     * @param string $roleName name of the role to assign to the user
     * @param bool $isActive whether the account should be active immediately
     */
    public function __construct(
        private string $name,
        private string $email,
        private string $password,
        private string $roleName,
        private bool $isActive = true,
    ) {}

    /**
     * Returns the user's first and last name.
     *
     * @return string full name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the user's email address.
     *
     * @return string email address
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Returns the user's password.
     *
     * @return string plain-text password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Returns the name of the role to be assigned to the user.
     *
     * @return string role name
     */
    public function getRoleName(): string
    {
        return $this->roleName;
    }

    /**
     * Indicates whether the user's account should be active.
     *
     * @return bool true when the account should be active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Converts the DTO to an array ready for database persistence (uses DB column names).
     *
     * @return array<string, mixed> user data as a key-value array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->roleName,
            'is_active' => $this->isActive,
        ];
    }
}
