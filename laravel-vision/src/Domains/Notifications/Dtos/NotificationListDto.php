<?php

namespace Notifications\Dtos;

/**
 * DTO carrying the parameters required to list notifications for a single user.
 */
readonly class NotificationListDto
{
    /**
     * Builds the DTO with the user id and the upper bound on returned rows.
     *
     * @param int $userId owner of the notifications
     * @param int $limit maximum number of rows to return
     */
    public function __construct(
        private int $userId,
        private int $limit,
    ) {}

    /**
     * Returns the owner user id.
     *
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Returns the maximum number of notifications to return.
     *
     * @return int row limit
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
