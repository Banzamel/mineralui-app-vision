<?php

namespace Tests\Unit\Services\Administration;

use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\UserActivityService;
use Auth\Models\AuthLog;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class UserActivityServiceTest extends TestCase
{
    private AuthLogRepositoryInterface $authLogs;
    private UserRepositoryInterface $users;
    private UserActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authLogs = Mockery::mock(AuthLogRepositoryInterface::class);
        $this->users = Mockery::mock(UserRepositoryInterface::class);
        $this->service = new UserActivityService($this->authLogs, $this->users);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_normalize_activity_type_maps_known_actions(): void
    {
        $this->assertSame('login', $this->invokePrivate('normalizeActivityType', 'login'));
        $this->assertSame('logout', $this->invokePrivate('normalizeActivityType', 'logout'));
        $this->assertSame('password_reset', $this->invokePrivate('normalizeActivityType', 'password_reset'));
        $this->assertSame('scopes_updated', $this->invokePrivate('normalizeActivityType', 'scopes_changed'));
        $this->assertSame('role_changed', $this->invokePrivate('normalizeActivityType', 'role_assigned'));
        $this->assertSame('model_change', $this->invokePrivate('normalizeActivityType', 'created'));
        $this->assertSame('model_change', $this->invokePrivate('normalizeActivityType', 'updated'));
        $this->assertSame('model_change', $this->invokePrivate('normalizeActivityType', 'deleted'));
        $this->assertSame('custom_action', $this->invokePrivate('normalizeActivityType', 'custom_action'));
    }

    public function test_my_activity_type_distinguishes_avatar_from_profile_updates(): void
    {
        $log = new AuthLog();
        $log->action = 'updated';
        $log->model = \Administration\Models\User::class;
        $log->changes = ['avatar_path' => 'new.jpg'];
        $this->assertSame('avatar_changed', $this->invokePrivate('myActivityType', $log));

        $log2 = new AuthLog();
        $log2->action = 'updated';
        $log2->model = \Administration\Models\User::class;
        $log2->changes = ['name' => 'Nowy'];
        $this->assertSame('profile_updated', $this->invokePrivate('myActivityType', $log2));
    }

    public function test_my_activity_type_recognises_album_and_photo_creates(): void
    {
        $album = new AuthLog();
        $album->action = 'created';
        $album->model = \Albums\Models\Album::class;
        $this->assertSame('album_created', $this->invokePrivate('myActivityType', $album));

        $photo = new AuthLog();
        $photo->action = 'created';
        $photo->model = \Albums\Models\Photo::class;
        $this->assertSame('photo_uploaded', $this->invokePrivate('myActivityType', $photo));
    }

    public function test_model_label_returns_english_noun(): void
    {
        $this->assertSame('object', $this->invokePrivate('modelLabel', \Objects\Models\VisionObject::class));
        $this->assertSame('camera', $this->invokePrivate('modelLabel', \Objects\Models\Camera::class));
        $this->assertSame('album', $this->invokePrivate('modelLabel', \Albums\Models\Album::class));
        $this->assertSame('user', $this->invokePrivate('modelLabel', \Administration\Models\User::class));
        $this->assertSame('record', $this->invokePrivate('modelLabel', 'Some\Unknown\Class'));
    }

    public function test_summarize_changes_skips_timestamps(): void
    {
        $this->assertSame(
            ' (name, email)',
            $this->invokePrivate('summarizeChanges', ['name' => 'a', 'email' => 'b', 'updated_at' => 'x']),
        );
    }

    public function test_summarize_changes_returns_empty_for_only_timestamps(): void
    {
        $this->assertSame('', $this->invokePrivate('summarizeChanges', ['updated_at' => 'x', 'created_at' => 'y']));
    }

    public function test_summarize_changes_truncates_after_four_keys(): void
    {
        $changes = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $this->assertSame(' (a, b, c, d, …)', $this->invokePrivate('summarizeChanges', $changes));
    }

    private function invokePrivate(string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($this->service, ...$args);
    }
}
