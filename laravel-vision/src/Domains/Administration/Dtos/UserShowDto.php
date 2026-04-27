<?php

namespace Administration\Dtos;

/**
 * Input DTO for retrieving a single user by id.
 */
readonly class UserShowDto
{
    /**
     * @param int $userId user id
     */
    public function __construct(
        private int $userId,
    ) {}

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
