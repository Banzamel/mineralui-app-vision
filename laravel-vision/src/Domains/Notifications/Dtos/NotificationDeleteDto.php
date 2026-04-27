<?php

namespace Notifications\Dtos;

/**
 * DTO carrying the ownership check pair for a single-notification delete action.
 */
readonly class NotificationDeleteDto
{
    /**
     * Builds the DTO with the acting user id and the target notification id.
     *
     * @param int $userId user performing the action
     * @param int $notificationId notification being deleted
     */
    public function __construct(
        private int $userId,
        private int $notificationId,
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

    /**
     * Returns the target notification id.
     *
     * @return int notification id
     */
    public function getNotificationId(): int
    {
        return $this->notificationId;
    }
}
