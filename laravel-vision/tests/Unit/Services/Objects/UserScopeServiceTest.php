<?php

namespace Tests\Unit\Services\Objects;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Objects\Repositories\Interfaces\UserScopeRepositoryInterface;
use Objects\Services\UserScopeService;
use Tests\TestCase;

class UserScopeServiceTest extends TestCase
{
    private UserScopeRepositoryInterface $repo;
    private UserScopeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(UserScopeRepositoryInterface::class);
        $this->service = new UserScopeService($this->repo);

        DB::shouldReceive('transaction')->andReturnUsing(fn($cb) => $cb());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_for_user_delegates_to_repository(): void
    {
        $collection = new Collection();
        $this->repo->shouldReceive('forUser')->once()->with(7)->andReturn($collection);

        $this->assertSame($collection, $this->service->forUser(7));
    }

    public function test_sync_replaces_all_scopes_in_a_single_transaction(): void
    {
        $finalCollection = new Collection();
        $this->repo->shouldReceive('deleteAllForUser')->once()->with(5);

        // Each scope row is created with the user id stitched in and scope_id stringified
        // (storage column is varchar to support uuid-style ids in the future).
        $this->repo->shouldReceive('create')->once()->with([
            'user_id' => 5,
            'type' => 'building',
            'scope_id' => '10',
        ]);
        $this->repo->shouldReceive('create')->once()->with([
            'user_id' => 5,
            'type' => 'camera',
            'scope_id' => '101',
        ]);

        $this->repo->shouldReceive('forUser')->once()->with(5)->andReturn($finalCollection);

        $result = $this->service->sync(5, [
            ['type' => 'building', 'scope_id' => 10],
            ['type' => 'camera', 'scope_id' => 101],
        ]);

        $this->assertSame($finalCollection, $result);
    }

    public function test_sync_with_empty_array_clears_all_scopes(): void
    {
        $finalCollection = new Collection();
        $this->repo->shouldReceive('deleteAllForUser')->once()->with(5);
        $this->repo->shouldNotReceive('create');
        $this->repo->shouldReceive('forUser')->once()->with(5)->andReturn($finalCollection);

        $result = $this->service->sync(5, []);

        $this->assertSame($finalCollection, $result);
    }
}
