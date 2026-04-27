<?php

namespace Auth\Dtos;

/**
 * Object holding user login data (email and password).
 */
readonly class LoginDto
{
    /**
     * Creates a new login data object.
     *
     * @param string $email User's email address
     * @param string $password User's password
     */
    public function __construct(
        private string $email,
        private string $password
    ) {
    }

    /**
     * Returns login data as an array.
     *
     * @return array Array containing email and password
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password
        ];
    }

    /**
     * Returns the user's email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Returns the user's password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
