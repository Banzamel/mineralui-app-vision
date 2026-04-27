<?php

namespace Tests\Unit\Services\Albums;

use Albums\Models\Photo;
use Albums\Services\PhotoStreamService;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PhotoStreamServiceTest extends TestCase
{
    private FileManagerServiceInterface $fileManager;
    private PhotoStreamService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileManager = Mockery::mock(FileManagerServiceInterface::class);
        $this->service = new PhotoStreamService($this->fileManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_stream_throws_when_photo_has_no_album(): void
    {
        $photo = Mockery::mock(Photo::class)->makePartial();
        $photo->shouldReceive('getAttribute')->with('album')->andReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->service->stream($photo);
    }

    public function test_stream_throws_when_album_has_no_file_manager_path(): void
    {
        $album = new \Albums\Models\Album();
        $album->file_manager_path_id = null;

        $photo = Mockery::mock(Photo::class)->makePartial();
        $photo->shouldReceive('getAttribute')->with('album')->andReturn($album);

        $this->expectException(NotFoundHttpException::class);
        $this->service->stream($photo);
    }
}
