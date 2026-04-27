<?php

namespace Tests\Unit\Services\Albums;

use Albums\Dtos\PhotoListDto;
use Albums\Events\AlbumDeletedEvent;
use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\AlbumService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class AlbumServiceTest extends TestCase
{
    private AlbumRepositoryInterface $albumRepo;
    private PhotoRepositoryInterface $photoRepo;
    private AlbumService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->albumRepo = Mockery::mock(AlbumRepositoryInterface::class);
        $this->photoRepo = Mockery::mock(PhotoRepositoryInterface::class);
        $this->service = new AlbumService($this->albumRepo, $this->photoRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_returns_repository_collection(): void
    {
        $collection = new Collection([new Album(), new Album()]);
        $this->albumRepo->shouldReceive('all')->once()->andReturn($collection);

        $result = $this->service->list();

        $this->assertSame($collection, $result);
    }

    public function test_by_camera_delegates_to_repository(): void
    {
        $collection = new Collection([new Album()]);
        $this->albumRepo->shouldReceive('byCamera')->once()->with(42)->andReturn($collection);

        $result = $this->service->byCamera(42);

        $this->assertSame($collection, $result);
    }

    public function test_find_delegates_to_repository(): void
    {
        $album = new Album();
        $this->albumRepo->shouldReceive('findOrFail')->once()->with(7)->andReturn($album);

        $result = $this->service->find(7);

        $this->assertSame($album, $result);
    }

    public function test_delete_runs_in_transaction_and_fires_event(): void
    {
        Event::fake([AlbumDeletedEvent::class]);
        // DB::transaction() — execute the closure passed in immediately, no real DB needed.
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($cb) => $cb());

        $album = new Album();
        $album->id = 11;
        $album->company_id = 100;
        $album->camera_id = 200;

        $this->albumRepo->shouldReceive('findOrFail')->once()->with(11)->andReturn($album);
        $this->photoRepo->shouldReceive('deleteByAlbum')->once()->with(11);
        $this->albumRepo->shouldReceive('delete')->once()->with($album);

        $this->service->delete(11);

        Event::assertDispatched(
            AlbumDeletedEvent::class,
            fn(AlbumDeletedEvent $e) => $e->albumId === 11 && $e->companyId === 100 && $e->cameraId === 200,
        );
    }

    public function test_list_photos_passes_dto_fields_to_photo_repository(): void
    {
        $dto = new PhotoListDto(albumId: 5, perPage: 50, cursor: 'cursor-token');
        $paginator = Mockery::mock(CursorPaginator::class);

        $this->photoRepo
            ->shouldReceive('cursorByAlbum')
            ->once()
            ->with(5, 50, 'cursor-token')
            ->andReturn($paginator);

        $result = $this->service->listPhotos($dto);

        $this->assertSame($paginator, $result);
    }

    public function test_list_photos_passes_null_cursor_through(): void
    {
        $dto = new PhotoListDto(albumId: 5, perPage: 25, cursor: null);
        $paginator = Mockery::mock(CursorPaginator::class);

        $this->photoRepo
            ->shouldReceive('cursorByAlbum')
            ->once()
            ->with(5, 25, null)
            ->andReturn($paginator);

        $result = $this->service->listPhotos($dto);

        $this->assertSame($paginator, $result);
    }
}
