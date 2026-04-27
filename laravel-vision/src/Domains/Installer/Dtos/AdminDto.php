<?php

namespace Installer\Dtos;

/**
 * DTO carrying administrator account data during installation.
 */
final readonly class AdminDto
{
    /**
     * @param string $name Administrator full name.
     * @param string $email Administrator e-mail address.
     * @param string $password Plain text password (before hashing).
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Returns the data as an associative array.
     *
     * @return array{name: string, email: string, password: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
