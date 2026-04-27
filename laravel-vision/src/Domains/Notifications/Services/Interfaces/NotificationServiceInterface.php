<?php

namespace Notifications\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Notifications\Dtos\NotificationCreateDto;
use Notifications\Dtos\NotificationDeleteAllDto;
use Notifications\Dtos\NotificationDeleteDto;
use Notifications\Dtos\NotificationListDto;
use Notifications\Dtos\NotificationMarkAllReadDto;
use Notifications\Dtos\NotificationMarkReadDto;
use Notifications\Dtos\NotificationUnreadCountDto;
use Notifications\Models\Notification;
use Shared\Exceptions\ApiJsonException;

/**
 * Per-user notifications service contract — list, count, mark read and delete operations.
 * All inputs flow through DTOs so the service never reads globals such as auth().
 */
interface NotificationServiceInterface
{
    /**
     * Returns a list of notifications owned by the user carried in the DTO, newest first.
     *
     * @param NotificationListDto $dto list query parameters
     * @return Collection<int, Notification>
     */
    public function list(NotificationListDto $dto): Collection;

    /**
     * Returns the count of unread notifications for the user carried in the DTO.
     *
     * @param NotificationUnreadCountDto $dto unread-count query parameters
     * @return int unread notifications count
     */
    public function unreadCount(NotificationUnreadCountDto $dto): int;

    /**
     * Persists a new notification and dispatches the broadcast event.
     *
     * @param NotificationCreateDto $dto full notification payload
     * @return Notification freshly created notification
     */
    public function create(NotificationCreateDto $dto): Notification;

    /**
     * Marks the notification carried in the DTO as read, enforcing ownership.
     *
     * @param NotificationMarkReadDto $dto user and notification id pair
     * @return void
     * @throws ApiJsonException when the notification does not exist or belongs to another user
     */
    public function markRead(NotificationMarkReadDto $dto): void;

    /**
     * Marks all unread notifications of the user carried in the DTO as read.
     *
     * @param NotificationMarkAllReadDto $dto acting user id wrapper
     * @return int number of rows updated
     */
    public function markAllRead(NotificationMarkAllReadDto $dto): int;

    /**
     * Deletes the notification carried in the DTO, enforcing ownership.
     *
     * @param NotificationDeleteDto $dto user and notification id pair
     * @return void
     * @throws ApiJsonException when the notification does not exist or belongs to another user
     */
    public function delete(NotificationDeleteDto $dto): void;

    /**
     * Deletes all notifications of the user carried in the DTO.
     *
     * @param NotificationDeleteAllDto $dto acting user id wrapper
     * @return int number of rows deleted
     */
    public function deleteAll(NotificationDeleteAllDto $dto): int;
}
