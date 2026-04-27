<?php

namespace Tests\Unit\Services\Albums;

use Albums\Models\Album;
use Albums\Models\Photo;
use Albums\Services\ThumbnailService;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ThumbnailServiceTest extends TestCase
{
    private FileManagerServiceInterface $fileManager;
    private ThumbnailService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileManager = Mockery::mock(FileManagerServiceInterface::class);
        $this->service = new ThumbnailService($this->fileManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_returns_false_when_album_has_no_path(): void
    {
        $album = new Album();
        $album->file_manager_path_id = null;

        $photo = Mockery::mock(Photo::class)->makePartial();
        $photo->shouldReceive('getAttribute')->with('album')->andReturn($album);

        $this->assertFalse($this->service->generate($photo));
    }

    public function test_generate_returns_false_when_photo_has_no_album(): void
    {
        $photo = Mockery::mock(Photo::class)->makePartial();
        $photo->shouldReceive('getAttribute')->with('album')->andReturn(null);

        $this->assertFalse($this->service->generate($photo));
    }

    public function test_stream_throws_when_album_has_no_path(): void
    {
        $album = new Album();
        $album->file_manager_path_id = null;

        $photo = Mockery::mock(Photo::class)->makePartial();
        $photo->shouldReceive('getAttribute')->with('album')->andReturn($album);

        $this->expectException(NotFoundHttpException::class);
        $this->service->stream($photo);
    }

    public function test_thumb_filename_swaps_extension_to_jpg(): void
    {
        // Private helper — we want to lock down the contract that thumbs always end in `.jpg`,
        // regardless of the source extension (jpeg/png/webp/etc).
        $this->assertSame('photo.jpg', $this->callThumbFilename('photo.jpeg'));
        $this->assertSame('photo.jpg', $this->callThumbFilename('photo.png'));
        $this->assertSame('photo.jpg', $this->callThumbFilename('photo.webp'));
        $this->assertSame('IMG_1234.jpg', $this->callThumbFilename('IMG_1234.JPG'));
    }

    private function callThumbFilename(string $filename): string
    {
        $ref = new ReflectionClass($this->service);
        $method = $ref->getMethod('thumbFilename');
        $method->setAccessible(true);
        return $method->invoke($this->service, $filename);
    }
}
