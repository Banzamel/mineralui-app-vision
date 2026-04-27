<?php

namespace Tests\Unit\Services\Notifications;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Notifications\Dtos\NotificationCreateDto;
use Notifications\Dtos\NotificationDeleteAllDto;
use Notifications\Dtos\NotificationDeleteDto;
use Notifications\Dtos\NotificationListDto;
use Notifications\Dtos\NotificationMarkAllReadDto;
use Notifications\Dtos\NotificationMarkReadDto;
use Notifications\Dtos\NotificationUnreadCountDto;
use Notifications\Enums\NotificationSeverityEnum;
use Notifications\Events\NotificationCreatedEvent;
use Notifications\Models\Notification;
use Notifications\Repositories\Interfaces\NotificationRepositoryInterface;
use Notifications\Services\NotificationService;
use Shared\Exceptions\ApiJsonException;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationRepositoryInterface $repo;
    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(NotificationRepositoryInterface::class);
        $this->service = new NotificationService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_delegates_to_repository(): void
    {
        $collection = new Collection();
        $this->repo->shouldReceive('listForUser')->once()->with(1, 50)->andReturn($collection);

        $this->assertSame($collection, $this->service->list(new NotificationListDto(userId: 1, limit: 50)));
    }

    public function test_unread_count_delegates_to_repository(): void
    {
        $this->repo->shouldReceive('countUnread')->once()->with(1)->andReturn(7);

        $this->assertSame(7, $this->service->unreadCount(new NotificationUnreadCountDto(userId: 1)));
    }

    public function test_create_persists_then_dispatches_created_event(): void
    {
        Event::fake([NotificationCreatedEvent::class]);
        $notification = new Notification();
        $dto = new NotificationCreateDto(
            companyId: 100,
            userId: 5,
            type: 'album_created',
            severity: NotificationSeverityEnum::Info,
            title: 'Hello',
            message: 'World',
            link: '/albums/1',
        );

        $this->repo->shouldReceive('create')->once()->with($dto->toArray())->andReturn($notification);

        $result = $this->service->create($dto);

        $this->assertSame($notification, $result);
        Event::assertDispatched(NotificationCreatedEvent::class);
    }

    public function test_mark_read_throws_when_notification_does_not_belong_to_user(): void
    {
        $this->repo->shouldReceive('findForUser')->once()->with(1, 99)->andReturn(null);

        $this->expectException(ApiJsonException::class);
        $this->service->markRead(new NotificationMarkReadDto(userId: 1, notificationId: 99));
    }

    public function test_mark_read_marks_when_owned(): void
    {
        $notification = new Notification();
        $this->repo->shouldReceive('findForUser')->once()->with(1, 99)->andReturn($notification);
        $this->repo->shouldReceive('markRead')->once()->with($notification);

        $this->service->markRead(new NotificationMarkReadDto(userId: 1, notificationId: 99));

        $this->assertTrue(true);
    }

    public function test_mark_all_read_returns_count_from_repository(): void
    {
        $this->repo->shouldReceive('markAllRead')->once()->with(1)->andReturn(12);

        $this->assertSame(12, $this->service->markAllRead(new NotificationMarkAllReadDto(userId: 1)));
    }

    public function test_delete_throws_when_notification_does_not_belong_to_user(): void
    {
        $this->repo->shouldReceive('findForUser')->once()->andReturn(null);

        $this->expectException(ApiJsonException::class);
        $this->service->delete(new NotificationDeleteDto(userId: 1, notificationId: 99));
    }

    public function test_delete_all_returns_count_from_repository(): void
    {
        $this->repo->shouldReceive('deleteAll')->once()->with(1)->andReturn(20);

        $this->assertSame(20, $this->service->deleteAll(new NotificationDeleteAllDto(userId: 1)));
    }
}
