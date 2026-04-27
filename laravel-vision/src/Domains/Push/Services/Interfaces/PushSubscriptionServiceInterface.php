<?php

namespace Push\Services\Interfaces;

use Push\Dtos\PushSubscriptionDto;
use Push\Models\PushSubscription;

/**
 * Web-push subscriptions management service contract.
 */
interface PushSubscriptionServiceInterface
{
    /**
     * Persists (insert or update) a push subscription for the given user.
     *
     * @param int $userId subscription owner
     * @param PushSubscriptionDto $dto subscription payload
     * @return PushSubscription upserted subscription row
     */
    public function save(int $userId, PushSubscriptionDto $dto): PushSubscription;

    /**
     * Removes the push subscription with the given endpoint URL (browser unsubscribed).
     *
     * @param int $userId subscription owner
     * @param string $endpoint endpoint url
     * @return void
     */
    public function delete(int $userId, string $endpoint): void;
}
