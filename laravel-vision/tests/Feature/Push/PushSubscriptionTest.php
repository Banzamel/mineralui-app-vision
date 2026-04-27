<?php

namespace Tests\Feature\Push;

use Tests\Feature\ApiTestCase;

class PushSubscriptionTest extends ApiTestCase
{
    public function test_authenticated_user_can_save_subscription(): void
    {
        $this->actingAsOperator();

        $response = $this->postJson('/api/vision/push/subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'keys' => [
                'p256dh' => 'BPublicKeyExampleValueHere_0000',
                'auth' => 'AuthKeyExampleValueHere_0000',
            ],
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('vision_push_subscriptions', [
            'user_id' => $this->operator->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }

    public function test_missing_endpoint_fails_validation(): void
    {
        $this->actingAsOperator();

        $response = $this->postJson('/api/vision/push/subscriptions', [
            'keys' => [
                'p256dh' => 'x',
                'auth' => 'y',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['endpoint']);
    }

    public function test_upsert_by_same_p256dh_keeps_one_row(): void
    {
        $this->actingAsOperator();

        $payload = [
            'endpoint' => 'https://endpoint-1',
            'keys' => [
                'p256dh' => 'SAME_KEY',
                'auth' => 'auth-1',
            ],
        ];
        $this->postJson('/api/vision/push/subscriptions', $payload)->assertCreated();

        $payload['endpoint'] = 'https://endpoint-2';
        $this->postJson('/api/vision/push/subscriptions', $payload)->assertCreated();

        $this->assertDatabaseCount('vision_push_subscriptions', 1);
    }
}
