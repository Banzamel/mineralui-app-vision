<?php

namespace Push\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Push\Models\PushSubscription;

/**
 * Web-push subscriptions repository contract.
 */
interface PushSubscriptionRepositoryInterface
{
    /**
     * All push subscriptions registered for a user.
     *
     * @param int $userId user id
     * @return Collection<int, PushSubscription>
     */
    public function forUser(int $userId): Collection;

    /**
     * Inserts or updates a subscription identified by (user_id, p256dh).
     *
     * @param array<string, mixed> $data row data
     * @return PushSubscription upserted row
     */
    public function upsert(array $data): PushSubscription;

    /**
     * Removes a subscription by its endpoint URL (called when the browser unsubscribes).
     *
     * @param int $userId user id
     * @param string $endpoint endpoint url
     * @return void
     */
    public function deleteByEndpoint(int $userId, string $endpoint): void;
}
