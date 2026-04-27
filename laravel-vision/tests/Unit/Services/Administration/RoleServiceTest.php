<?php

namespace Tests\Unit\Services\Administration;

use Administration\Dtos\RoleDto;
use Administration\Events\RoleCreatedEvent;
use Administration\Events\RoleDeletedEvent;
use Administration\Events\RoleUpdatedEvent;
use Auth\Models\User;
use Administration\Repositories\Interfaces\RoleRepositoryInterface;
use Administration\Services\RoleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    private RoleRepositoryInterface $repo;
    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(RoleRepositoryInterface::class);
        $this->service = new RoleService($this->repo);

        // Events demand a non-null actor; using `be()` populates auth()->user() without DB.
        $actor = new User();
        $actor->id = 1;
        $actor->name = 'Tester';
        $this->be($actor);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_roles_delegates_to_repository(): void
    {
        $collection = new Collection([new Role(), new Role()]);
        $this->repo->shouldReceive('findAll')->once()->andReturn($collection);

        $this->assertSame($collection, $this->service->getAllRoles());
    }

    public function test_create_role_persists_and_dispatches_event(): void
    {
        Event::fake([RoleCreatedEvent::class]);
        $dto = new RoleDto('Admin', ['users.view']);
        $role = new Role();
        $role->company_id = 1;

        $this->repo->shouldReceive('create')->once()->with($dto)->andReturn($role);

        $result = $this->service->createRole($dto);

        $this->assertSame($role, $result);
        Event::assertDispatched(RoleCreatedEvent::class);
    }

    public function test_update_role_finds_existing_then_updates_and_dispatches_event(): void
    {
        Event::fake([RoleUpdatedEvent::class]);
        $dto = new RoleDto('Editor', ['albums.view']);
        $existing = new Role();
        $updated = new Role();
        $updated->company_id = 1;

        $this->repo->shouldReceive('findOrFail')->once()->with(7)->andReturn($existing);
        $this->repo->shouldReceive('update')->once()->with($existing, $dto)->andReturn($updated);

        $result = $this->service->updateRole(7, $dto);

        $this->assertSame($updated, $result);
        Event::assertDispatched(RoleUpdatedEvent::class);
    }

    public function test_delete_role_dispatches_event_then_deletes(): void
    {
        Event::fake([RoleDeletedEvent::class]);
        $role = new Role();
        $role->id = 3;
        $role->name = 'Editor';
        $role->company_id = 1;

        $this->repo->shouldReceive('findOrFail')->once()->with(3)->andReturn($role);
        $this->repo->shouldReceive('delete')->once()->with($role)->andReturn(true);

        $this->assertTrue($this->service->deleteRole(3));
        Event::assertDispatched(RoleDeletedEvent::class);
    }
}
