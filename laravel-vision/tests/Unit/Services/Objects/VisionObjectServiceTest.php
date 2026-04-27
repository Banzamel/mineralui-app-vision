<?php

namespace Tests\Unit\Services\Objects;

use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Objects\Repositories\Interfaces\VisionObjectRepositoryInterface;
use Objects\Services\VisionObjectService;
use ReflectionClass;
use Tests\TestCase;

class VisionObjectServiceTest extends TestCase
{
    private VisionObjectRepositoryInterface $repo;
    private VisionObjectService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(VisionObjectRepositoryInterface::class);
        $this->service = new VisionObjectService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_delegates_to_repository(): void
    {
        $collection = new Collection();
        $this->repo->shouldReceive('all')->once()->andReturn($collection);

        $this->assertSame($collection, $this->service->list());
    }

    public function test_calculate_depth_returns_zero_for_root(): void
    {
        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('calculateDepth');
        $m->setAccessible(true);

        $this->assertSame(0, $m->invoke($this->service, null));
    }

    public function test_calculate_depth_increments_parent_depth(): void
    {
        $this->repo->shouldReceive('depthOf')->once()->with(7)->andReturn(2);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('calculateDepth');
        $m->setAccessible(true);

        $this->assertSame(3, $m->invoke($this->service, 7));
    }

    public function test_calculate_depth_falls_back_to_zero_when_parent_missing(): void
    {
        // depthOf returning null = parent row went away mid-flight; tree should still accept the new
        // child as a root rather than nulling out the column.
        $this->repo->shouldReceive('depthOf')->once()->with(99)->andReturn(null);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('calculateDepth');
        $m->setAccessible(true);

        $this->assertSame(0, $m->invoke($this->service, 99));
    }

    public function test_unique_slug_falls_back_to_object_for_empty_input(): void
    {
        $this->repo->shouldReceive('slugExists')->with('object')->andReturn(false);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('uniqueSlug');
        $m->setAccessible(true);

        $this->assertSame('object', $m->invoke($this->service, ''));
    }

    public function test_unique_slug_appends_counter_for_collisions(): void
    {
        $this->repo->shouldReceive('slugExists')->with('budynek-glowny')->andReturn(true);
        $this->repo->shouldReceive('slugExists')->with('budynek-glowny-2')->andReturn(false);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('uniqueSlug');
        $m->setAccessible(true);

        $this->assertSame('budynek-glowny-2', $m->invoke($this->service, 'Budynek Główny'));
    }
}
