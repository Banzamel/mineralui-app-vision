<?php

namespace Tests\Unit\Services\System;

use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Mockery;
use System\Dtos\SystemStatusQueryDto;
use System\Services\SystemStatusService;
use Tests\TestCase;

class SystemStatusServiceTest extends TestCase
{
    private PhotoRepositoryInterface $photos;
    private SystemStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photos = Mockery::mock(PhotoRepositoryInterface::class);
        $this->service = new SystemStatusService($this->photos);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_current_returns_dto_with_used_bytes_from_repository(): void
    {
        Storage::fake('public');
        $this->photos->shouldReceive('sumBytesForCompany')->once()->with(100)->andReturn(1024);

        $dto = $this->service->current(new SystemStatusQueryDto(companyId: 100));

        $this->assertSame(1024, $dto->getDisk()->getUsedBytes());
        $this->assertGreaterThan(0, $dto->getDisk()->getTotalBytes());
        $this->assertGreaterThanOrEqual(0, $dto->getDisk()->getPercent());
        $this->assertLessThanOrEqual(100, $dto->getDisk()->getPercent());
    }

    public function test_current_returns_version_from_config(): void
    {
        Storage::fake('public');
        config(['vision.version' => '9.9.9-test']);

        $this->photos->shouldReceive('sumBytesForCompany')->andReturn(0);

        $dto = $this->service->current(new SystemStatusQueryDto(companyId: 100));

        $this->assertSame('9.9.9-test', $dto->getVersion());
    }
}
