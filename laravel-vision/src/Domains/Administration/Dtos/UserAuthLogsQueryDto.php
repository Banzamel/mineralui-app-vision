<?php

namespace Administration\Dtos;

/**
 * Input DTO for paginating auth log entries of a single user.
 */
readonly class UserAuthLogsQueryDto
{
    /**
     * @param int $userId user whose log is being read
     * @param int $perPage rows per page
     */
    public function __construct(
        private int $userId,
        private int $perPage,
    ) {}

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int rows per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
