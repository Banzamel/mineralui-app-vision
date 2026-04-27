<?php

namespace Notifications\Dtos;

/**
 * DTO carrying the user id for an unread-count query.
 */
readonly class NotificationUnreadCountDto
{
    /**
     * Builds the DTO with the user whose unread notifications are being counted.
     *
     * @param int $userId owner of the notifications
     */
    public function __construct(
        private int $userId,
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
}
