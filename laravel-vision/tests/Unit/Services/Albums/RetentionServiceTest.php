<?php

namespace Tests\Unit\Services\Albums;

use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\RetentionService;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Enums\StoragesEnum;
use FileManager\Models\FileManagerPath;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Objects\Models\Camera;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;
use Tests\TestCase;

class RetentionServiceTest extends TestCase
{
    private AlbumRepositoryInterface $albums;
    private PhotoRepositoryInterface $photos;
    private FileManagerServiceInterface $fileManager;
    private CameraRepositoryInterface $cameras;
    private RetentionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->albums = Mockery::mock(AlbumRepositoryInterface::class);
        $this->photos = Mockery::mock(PhotoRepositoryInterface::class);
        $this->fileManager = Mockery::mock(FileManagerServiceInterface::class);
        $this->cameras = Mockery::mock(CameraRepositoryInterface::class);
        $this->service = new RetentionService($this->albums, $this->photos, $this->fileManager, $this->cameras);

        // Each iteration wraps work in DB::transaction; collapse to direct execution.
        DB::shouldReceive('transaction')->andReturnUsing(fn($cb) => $cb());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_purge_returns_zero_when_no_old_albums(): void
    {
        $this->albums->shouldReceive('olderThan')->once()->andReturn(new Collection());

        $this->assertSame(0, $this->service->purge(7));
    }

    public function test_purge_deletes_album_with_file_manager_path(): void
    {
        $album = new Album();
        $album->id = 1;
        $album->file_manager_path_id = 99;

        $this->albums->shouldReceive('olderThan')->once()->andReturn(new Collection([$album]));
        $this->photos->shouldReceive('deleteByAlbum')->once()->with(1);
        $this->fileManager
            ->shouldReceive('deleteItem')
            ->once()
            ->with(Mockery::on(fn(DeleteItemDto $dto) => $dto->getPathId() === 99));
        $this->albums->shouldReceive('delete')->once()->with($album);

        $this->assertSame(1, $this->service->purge(7));
    }

    public function test_purge_deletes_directory_by_camera_path_when_album_has_no_path_id(): void
    {
        $album = new Album();
        $album->id = 2;
        $album->camera_id = 8;
        $album->folder_name = '2026-05-01';
        $album->file_manager_path_id = null;

        $camera = new Camera();
        $camera->file_manager_path_id = 77;

        $root = new FileManagerPath();
        $root->path = '1/cam-8';
        $root->storage = StoragesEnum::local;

        Storage::fake('local');
        Storage::disk('local')->makeDirectory('1/cam-8/2026-05-01');
        $this->albums->shouldReceive('olderThan')->once()->andReturn(new Collection([$album]));
        $this->photos->shouldReceive('deleteByAlbum')->once()->with(2);
        $this->fileManager->shouldNotReceive('deleteItem');
        $this->cameras->shouldReceive('findOrFail')->once()->with(8)->andReturn($camera);
        $this->fileManager->shouldReceive('getItem')->once()->andReturn($root);
        $this->albums->shouldReceive('delete')->once()->with($album);

        $this->assertSame(1, $this->service->purge(7));
        Storage::disk('local')->assertMissing('1/cam-8/2026-05-01');
    }

    public function test_purge_returns_count_for_multiple_albums(): void
    {
        $a = new Album();
        $a->id = 1;
        $a->file_manager_path_id = null;
        $b = new Album();
        $b->id = 2;
        $b->file_manager_path_id = null;
        $c = new Album();
        $c->id = 3;
        $c->file_manager_path_id = null;

        $this->albums->shouldReceive('olderThan')->once()->andReturn(new Collection([$a, $b, $c]));
        $this->photos->shouldReceive('deleteByAlbum')->times(3);
        $this->albums->shouldReceive('delete')->times(3);

        $this->assertSame(3, $this->service->purge(14));
    }
}
