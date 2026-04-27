<?php

namespace Notifications\Services;

use Illuminate\Database\Eloquent\Collection;
use Notifications\Dtos\NotificationCreateDto;
use Notifications\Dtos\NotificationDeleteAllDto;
use Notifications\Dtos\NotificationDeleteDto;
use Notifications\Dtos\NotificationListDto;
use Notifications\Dtos\NotificationMarkAllReadDto;
use Notifications\Dtos\NotificationMarkReadDto;
use Notifications\Dtos\NotificationUnreadCountDto;
use Notifications\Events\NotificationCreatedEvent;
use Notifications\Models\Notification;
use Notifications\Repositories\Interfaces\NotificationRepositoryInterface;
use Notifications\Services\Interfaces\NotificationServiceInterface;
use Shared\Exceptions\ApiJsonException;

/**
 * Notifications service — creating, listing and reacting to user actions on notifications.
 * Operates exclusively on DTOs; auth context and cross-domain lookups happen at the edges.
 */
class NotificationService implements NotificationServiceInterface
{
    /**
     * @param NotificationRepositoryInterface $repository notifications repository
     */
    public function __construct(
        protected NotificationRepositoryInterface $repository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function list(NotificationListDto $dto): Collection
    {
        return $this->repository->listForUser($dto->getUserId(), $dto->getLimit());
    }

    /**
     * @inheritDoc
     */
    public function unreadCount(NotificationUnreadCountDto $dto): int
    {
        return $this->repository->countUnread($dto->getUserId());
    }

    /**
     * @inheritDoc
     */
    public function create(NotificationCreateDto $dto): Notification
    {
        $notification = $this->repository->create($dto->toArray());
        event(new NotificationCreatedEvent($notification));
        return $notification;
    }

    /**
     * @inheritDoc
     */
    public function markRead(NotificationMarkReadDto $dto): void
    {
        $notification = $this->mineOrFail($dto->getUserId(), $dto->getNotificationId());
        $this->repository->markRead($notification);
    }

    /**
     * @inheritDoc
     */
    public function markAllRead(NotificationMarkAllReadDto $dto): int
    {
        return $this->repository->markAllRead($dto->getUserId());
    }

    /**
     * @inheritDoc
     */
    public function delete(NotificationDeleteDto $dto): void
    {
        $notification = $this->mineOrFail($dto->getUserId(), $dto->getNotificationId());
        $this->repository->delete($notification);
    }

    /**
     * @inheritDoc
     */
    public function deleteAll(NotificationDeleteAllDto $dto): int
    {
        return $this->repository->deleteAll($dto->getUserId());
    }

    /**
     * Fetches a notification belonging to the given user or throws 404.
     *
     * @param int $userId acting user id
     * @param int $notificationId target notification id
     * @return Notification notification owned by the user
     * @throws ApiJsonException when the notification does not exist or belongs to another user
     */
    protected function mineOrFail(int $userId, int $notificationId): Notification
    {
        $notification = $this->repository->findForUser($userId, $notificationId);
        if (!$notification) {
            throw new ApiJsonException('Notification not found.', 404);
        }
        return $notification;
    }
}
