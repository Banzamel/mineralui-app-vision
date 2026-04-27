<?php

namespace Administration\Dtos;

/**
 * Input DTO for listing the caller's own recent activity entries.
 */
readonly class MyActivityQueryDto
{
    /**
     * @param int $userId acting user id
     * @param int $limit maximum number of entries to return
     */
    public function __construct(
        private int $userId,
        private int $limit,
    ) {}

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int row limit
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
