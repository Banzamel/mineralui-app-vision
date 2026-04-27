<?php

namespace Tests\Unit\Services\Push;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Mockery;
use Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface;
use Push\Services\WebPushSender;
use Tests\TestCase;

class WebPushSenderTest extends TestCase
{
    private PushSubscriptionRepositoryInterface $repo;
    private WebPushSender $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(PushSubscriptionRepositoryInterface::class);
        $this->service = new WebPushSender($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_zero_when_user_has_no_subscriptions(): void
    {
        $this->repo->shouldReceive('forUser')->once()->with(7)->andReturn(new Collection());

        $this->assertSame(0, $this->service->sendToUser(7, ['title' => 'X']));
    }

    public function test_returns_zero_when_vapid_keys_missing(): void
    {
        // Even with subscriptions, the sender bails out without any HTTP traffic when VAPID isn't set.
        $row = (object) [
            'endpoint' => 'https://fcm/x',
            'p256dh' => str_repeat('a', 87),
            'auth' => str_repeat('b', 22),
        ];
        $this->repo->shouldReceive('forUser')->once()->with(7)->andReturn(new Collection([$row]));

        Config::set('webpush.vapid', ['publicKey' => '', 'privateKey' => '']);

        $this->assertSame(0, $this->service->sendToUser(7, ['title' => 'X']));
    }
}
