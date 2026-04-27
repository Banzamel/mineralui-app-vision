<?php

namespace Push\Services;

use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface;
use Push\Services\Interfaces\WebPushSenderInterface;

/**
 * Adapter around minishlink/web-push. Reads VAPID config, fans out notifications and prunes
 * subscriptions whose endpoints reject delivery (404 / 410 = device unregistered the push token).
 */
class WebPushSender implements WebPushSenderInterface
{
    /**
     * @param PushSubscriptionRepositoryInterface $repository Subscription repo (read + cleanup).
     */
    public function __construct(
        protected PushSubscriptionRepositoryInterface $repository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendToUser(int $userId, array $payload): int
    {
        $subscriptions = $this->repository->forUser($userId);
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $vapid = config('webpush.vapid');
        if (empty($vapid['publicKey']) || empty($vapid['privateKey'])) {
            Log::warning('Web Push skipped: VAPID keys are not configured.');
            return 0;
        }

        $defaults = config('webpush.defaults', []);
        $webPush = new WebPush(['VAPID' => $vapid], $defaults);
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

        foreach ($subscriptions as $row) {
            $sub = Subscription::create([
                'endpoint' => $row->endpoint,
                'publicKey' => $row->p256dh,
                'authToken' => $row->auth,
                'contentEncoding' => 'aes128gcm',
            ]);
            $webPush->queueNotification($sub, $body);
        }

        $accepted = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $accepted++;
                continue;
            }
            // 404 or 410 = the browser unregistered the subscription. Drop it from the DB.
            $statusCode = $report->getResponse()?->getStatusCode();
            if ($statusCode === 404 || $statusCode === 410) {
                $this->repository->deleteByEndpoint($userId, $report->getRequest()->getUri()->__toString());
            } else {
                Log::warning('Web Push delivery failed', [
                    'user_id' => $userId,
                    'status' => $statusCode,
                    'reason' => $report->getReason(),
                ]);
            }
        }

        return $accepted;
    }
}
