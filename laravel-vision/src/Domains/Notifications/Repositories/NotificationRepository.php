<?php

namespace Notifications\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Notifications\Models\Notification;
use Notifications\Repositories\Interfaces\NotificationRepositoryInterface;

/**
 * Eloquent implementation of the notifications repository.
 */
class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function listForUser(int $userId, int $limit = 100): Collection
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function countUnread(int $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function findForUser(int $userId, int $id): ?Notification
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Notification
    {
        return Notification::query()->create($data);
    }

    /**
     * @inheritDoc
     */
    public function markRead(Notification $notification): Notification
    {
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }
        return $notification;
    }

    /**
     * @inheritDoc
     */
    public function markAllRead(int $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @inheritDoc
     */
    public function delete(Notification $notification): void
    {
        $notification->delete();
    }

    /**
     * @inheritDoc
     */
    public function deleteAll(int $userId): int
    {
        return Notification::query()->where('user_id', $userId)->delete();
    }
}
