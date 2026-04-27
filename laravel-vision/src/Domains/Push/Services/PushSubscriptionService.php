<?php

namespace Push\Services;

use Push\Dtos\PushSubscriptionDto;
use Push\Models\PushSubscription;
use Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface;
use Push\Services\Interfaces\PushSubscriptionServiceInterface;

/**
 * Web push subscription management service — thin orchestration over the repository.
 */
class PushSubscriptionService implements PushSubscriptionServiceInterface
{
    /**
     * @param PushSubscriptionRepositoryInterface $repository subscriptions repository
     */
    public function __construct(
        protected PushSubscriptionRepositoryInterface $repository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(int $userId, PushSubscriptionDto $dto): PushSubscription
    {
        return $this->repository->upsert(array_merge(
            ['user_id' => $userId],
            $dto->toArray(),
        ));
    }

    /**
     * @inheritDoc
     */
    public function delete(int $userId, string $endpoint): void
    {
        $this->repository->deleteByEndpoint($userId, $endpoint);
    }
}
