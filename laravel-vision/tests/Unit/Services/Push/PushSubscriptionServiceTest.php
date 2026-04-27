<?php

namespace Tests\Unit\Services\Push;

use Mockery;
use Push\Dtos\PushSubscriptionDto;
use Push\Models\PushSubscription;
use Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface;
use Push\Services\PushSubscriptionService;
use Tests\TestCase;

class PushSubscriptionServiceTest extends TestCase
{
    private PushSubscriptionRepositoryInterface $repo;
    private PushSubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(PushSubscriptionRepositoryInterface::class);
        $this->service = new PushSubscriptionService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_save_merges_user_id_into_dto_payload(): void
    {
        $row = new PushSubscription();
        $dto = new PushSubscriptionDto(
            endpoint: 'https://fcm/endpoint',
            p256dh: 'p256dh-key',
            auth: 'auth-key',
            userAgent: 'PHPUnit',
        );

        $this->repo->shouldReceive('upsert')->once()->with([
            'user_id' => 7,
            'endpoint' => 'https://fcm/endpoint',
            'p256dh' => 'p256dh-key',
            'auth' => 'auth-key',
            'user_agent' => 'PHPUnit',
        ])->andReturn($row);

        $this->assertSame($row, $this->service->save(7, $dto));
    }

    public function test_delete_delegates_to_repository(): void
    {
        $this->repo->shouldReceive('deleteByEndpoint')->once()->with(7, 'https://fcm/endpoint');

        $this->service->delete(7, 'https://fcm/endpoint');

        $this->assertTrue(true);
    }
}
