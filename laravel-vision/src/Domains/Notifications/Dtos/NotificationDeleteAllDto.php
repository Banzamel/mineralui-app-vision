<?php

namespace Notifications\Dtos;

/**
 * DTO carrying the user id for a delete-all-notifications action.
 */
readonly class NotificationDeleteAllDto
{
    /**
     * Builds the DTO with the user whose notifications are being wiped.
     *
     * @param int $userId acting user id
     */
    public function __construct(
        private int $userId,
    ) {}

    /**
     * Returns the acting user id.
     *
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
