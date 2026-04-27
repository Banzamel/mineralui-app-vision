<?php

namespace Push\Services\Interfaces;

/**
 * Sends Web Push messages signed with the configured VAPID keypair.
 * Implementations are expected to be no-ops (or throw quietly) when the user has no
 * subscriptions — this avoids forcing every caller to check first.
 */
interface WebPushSenderInterface
{
    /**
     * Pushes the same payload to every active subscription belonging to the given user.
     * Subscriptions returning 404/410 from the push service are deleted from the DB so
     * we don't keep retrying dead endpoints.
     *
     * @param int $userId Recipient user id.
     * @param array<string, mixed> $payload Notification body — usually `{title, message, link, icon}`.
     * @return int Number of pushes accepted by the push services.
     */
    public function sendToUser(int $userId, array $payload): int;
}
