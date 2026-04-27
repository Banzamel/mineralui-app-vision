<?php

namespace Notifications\Dtos;

/**
 * DTO carrying the user id for a mark-all-read action.
 */
readonly class NotificationMarkAllReadDto
{
    /**
     * Builds the DTO with the user whose unread notifications are being flipped.
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
