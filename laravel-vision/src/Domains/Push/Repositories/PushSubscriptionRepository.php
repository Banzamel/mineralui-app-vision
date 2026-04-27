<?php

namespace Push\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Push\Models\PushSubscription;
use Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface;

/**
 * Eloquent implementation of the push subscriptions repository.
 */
class PushSubscriptionRepository implements PushSubscriptionRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function forUser(int $userId): Collection
    {
        return PushSubscription::query()->where('user_id', $userId)->get();
    }

    /**
     * @inheritDoc
     */
    public function upsert(array $data): PushSubscription
    {
        return PushSubscription::query()->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'p256dh' => $data['p256dh'],
            ],
            $data,
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteByEndpoint(int $userId, string $endpoint): void
    {
        PushSubscription::query()
            ->where('user_id', $userId)
            ->where('endpoint', $endpoint)
            ->delete();
    }
}
