<?php

namespace Tests\Unit\Services\Objects;

use Albums\Services\Interfaces\AlbumServiceInterface;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Mockery;
use Objects\Models\Camera;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;
use Objects\Services\CameraService;
use ReflectionClass;
use Tests\TestCase;

class CameraServiceTest extends TestCase
{
    private CameraRepositoryInterface $repo;
    private FileManagerServiceInterface $fileManager;
    private AlbumServiceInterface $albums;
    private CameraService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(CameraRepositoryInterface::class);
        $this->fileManager = Mockery::mock(FileManagerServiceInterface::class);
        $this->albums = Mockery::mock(AlbumServiceInterface::class);
        $this->service = new CameraService($this->repo, $this->fileManager, $this->albums);
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

    public function test_by_object_delegates_to_repository(): void
    {
        $collection = new Collection();
        $this->repo->shouldReceive('byObject')->once()->with(15)->andReturn($collection);

        $this->assertSame($collection, $this->service->byObject(15));
    }

    public function test_decrypt_password_returns_null_for_camera_without_encrypted_password(): void
    {
        $camera = new Camera();
        $camera->stream_password_encrypted = null;

        $this->assertNull($this->service->decryptPassword($camera));
    }

    public function test_decrypt_password_uses_crypt_facade(): void
    {
        Crypt::shouldReceive('decryptString')->once()->with('encrypted-blob')->andReturn('plain-pass');

        $camera = new Camera();
        $camera->stream_password_encrypted = 'encrypted-blob';

        $this->assertSame('plain-pass', $this->service->decryptPassword($camera));
    }

    public function test_unique_slug_appends_counter_until_repository_reports_free(): void
    {
        // First three slug variants are taken, fourth is free — service should append "-2", "-3", "-4".
        $this->repo->shouldReceive('slugExists')->with('main-camera')->andReturn(true);
        $this->repo->shouldReceive('slugExists')->with('main-camera-2')->andReturn(true);
        $this->repo->shouldReceive('slugExists')->with('main-camera-3')->andReturn(true);
        $this->repo->shouldReceive('slugExists')->with('main-camera-4')->andReturn(false);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('uniqueSlug');
        $m->setAccessible(true);

        $this->assertSame('main-camera-4', $m->invoke($this->service, 'Main Camera'));
    }

    public function test_unique_slug_falls_back_to_camera_for_empty_strings(): void
    {
        $this->repo->shouldReceive('slugExists')->with('camera')->andReturn(false);

        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('uniqueSlug');
        $m->setAccessible(true);

        $this->assertSame('camera', $m->invoke($this->service, ''));
    }
}
