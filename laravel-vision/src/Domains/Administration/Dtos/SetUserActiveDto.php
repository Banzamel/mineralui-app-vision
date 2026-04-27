<?php

namespace Administration\Dtos;

/**
 * Input DTO for toggling the active flag on a user account.
 */
readonly class SetUserActiveDto
{
    /**
     * @param int $userId user id
     * @param bool $active new active flag
     */
    public function __construct(
        private int $userId,
        private bool $active,
    ) {}

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return bool desired active state
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
