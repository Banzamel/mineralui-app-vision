<?php

namespace Notifications\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Notifications\Models\Notification;

/**
 * Repository contract for the notifications table.
 */
interface NotificationRepositoryInterface
{
    /**
     * Returns the most recent notifications for a user, newest first.
     *
     * @param int $userId
     * @param int $limit
     * @return Collection<int, Notification>
     */
    public function listForUser(int $userId, int $limit = 100): Collection;

    /**
     * Counts unread notifications for a user.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int;

    /**
     * Finds a notification by id and ensures it belongs to the given user.
     *
     * @param int $userId
     * @param int $id
     * @return Notification|null
     */
    public function findForUser(int $userId, int $id): ?Notification;

    /**
     * Creates a new notification row.
     *
     * @param array<string, mixed> $data
     * @return Notification
     */
    public function create(array $data): Notification;

    /**
     * Marks the given notification as read (read_at = now()).
     *
     * @param Notification $notification
     * @return Notification
     */
    public function markRead(Notification $notification): Notification;

    /**
     * Marks all unread notifications of the user as read.
     *
     * @param int $userId
     * @return int number of rows updated
     */
    public function markAllRead(int $userId): int;

    /**
     * Deletes a notification.
     *
     * @param Notification $notification
     * @return void
     */
    public function delete(Notification $notification): void;

    /**
     * Deletes all notifications belonging to the user.
     *
     * @param int $userId
     * @return int number of rows deleted
     */
    public function deleteAll(int $userId): int;
}
