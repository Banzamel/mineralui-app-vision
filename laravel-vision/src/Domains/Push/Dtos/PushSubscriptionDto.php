<?php

namespace Push\Dtos;

/**
 * Web push subscription payload coming from the browser's PushManager (VAPID).
 */
final readonly class PushSubscriptionDto
{
    /**
     * @param string $endpoint push endpoint URL
     * @param string $p256dh device public key (base64url)
     * @param string $auth auth secret (base64url)
     * @param string|null $userAgent browser identifier — used when cleaning up old subscriptions
     */
    public function __construct(
        public string $endpoint,
        public string $p256dh,
        public string $auth,
        public ?string $userAgent = null,
    ) {
    }

    /**
     * Cast to the array shape expected by the repository.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'p256dh' => $this->p256dh,
            'auth' => $this->auth,
            'user_agent' => $this->userAgent,
        ];
    }
}
