<?php

namespace Tests\Unit\Services\Administration;

use Administration\Dtos\ResetUserPasswordDto;
use Administration\Dtos\SetUserActiveDto;
use Administration\Events\UserAvatarUpdatedEvent;
use Administration\Events\UserDeletedEvent;
use Administration\Events\UserUpdatedEvent;
use Administration\Models\User as AdminUser;
use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\UserManagementService;
use Auth\Models\User as ActorUser;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    private UserRepositoryInterface $users;
    private TokenRepositoryInterface $tokens;
    private AuthLogRepositoryInterface $authLogs;
    private UserManagementService $service;
    private ActorUser $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepositoryInterface::class);
        $this->tokens = Mockery::mock(TokenRepositoryInterface::class);
        $this->authLogs = Mockery::mock(AuthLogRepositoryInterface::class);

        $this->service = new UserManagementService($this->users, $this->tokens, $this->authLogs);

        // Events demand Auth\Models\User (Passport-aware subclass) for the acting user.
        $this->actor = new ActorUser();
        $this->actor->id = 1;
        $this->actor->company_id = 100;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_delete_dispatches_event_then_repository_deletes(): void
    {
        Event::fake([UserDeletedEvent::class]);
        $user = $this->makeUser(5);

        $this->users->shouldReceive('findOrFail')->once()->with(5)->andReturn($user);
        $this->users->shouldReceive('delete')->once()->with($user)->andReturn(true);

        $this->assertTrue($this->service->delete(5, $this->actor));
        Event::assertDispatched(UserDeletedEvent::class);
    }

    public function test_update_avatar_persists_then_dispatches_event(): void
    {
        Event::fake([UserAvatarUpdatedEvent::class]);

        $user = $this->makeUser(3);
        $userAfter = $this->makeUser(3);

        $file = UploadedFile::fake()->image('avatar.png');

        $this->users->shouldReceive('findOrFail')->once()->with(3)->andReturn($user);
        $this->users->shouldReceive('updateAvatar')->once()->with($user, $file)->andReturn($userAfter);

        $result = $this->service->updateAvatar(3, $file, $this->actor);

        $this->assertSame($userAfter, $result);
        Event::assertDispatched(UserAvatarUpdatedEvent::class);
    }

    public function test_set_active_writes_is_active_flag_and_dispatches_update_event(): void
    {
        Event::fake([UserUpdatedEvent::class]);
        $dto = new SetUserActiveDto(userId: 8, active: false);

        $user = $this->makeUser(8);
        $userAfter = $this->makeUser(8);

        $this->users->shouldReceive('findOrFail')->once()->with(8)->andReturn($user);
        $this->users
            ->shouldReceive('update')
            ->once()
            ->with($user, ['is_active' => false])
            ->andReturn($userAfter);

        $this->service->setActive($dto, $this->actor);

        Event::assertDispatched(UserUpdatedEvent::class);
    }

    public function test_reset_password_generates_random_password_revokes_tokens_and_logs(): void
    {
        $dto = new ResetUserPasswordDto(userId: 9, ip: '127.0.0.1', userAgent: 'PHPUnit');

        $user = new AdminUser();
        $user->id = 9;
        $user->company_id = 100;

        $this->users->shouldReceive('findOrFail')->once()->with(9)->andReturn($user);
        $this->users->shouldReceive('update')->once()->with(
            $user,
            Mockery::on(fn($payload) => isset($payload['password']) && strlen($payload['password']) === 12),
        )->andReturn($user);
        $this->tokens->shouldReceive('revokeAllForUser')->once()->with(9);
        $this->authLogs->shouldReceive('log')->once();

        $plain = $this->service->resetPassword($dto, $this->actor);

        $this->assertSame(12, strlen($plain));
    }

    /**
     * Returns a User mock that allows `->load('roles:id,name')` to be called
     * fluently — used by service methods after repository writes.
     */
    private function makeUser(int $id): AdminUser
    {
        $user = Mockery::mock(AdminUser::class)->makePartial();
        $user->id = $id;
        $user->name = "User {$id}";
        $user->email = "user{$id}@vision.local";
        $user->company_id = 100;
        $user->shouldReceive('load')->andReturnSelf()->byDefault();
        return $user;
    }
}
