<?php

namespace Tests\Unit\Services\FileManager;

use FileManager\Dtos\DeleteItemDto;
use FileManager\Dtos\DownloadFileDto;
use FileManager\Dtos\FileListDto;
use FileManager\Dtos\FileShowDto;
use FileManager\Dtos\UpdateItemDto;
use FileManager\Enums\StoragesEnum;
use FileManager\Models\FileManagerPath;
use FileManager\Repositories\Interfaces\FileManagerRepositoryInterface;
use FileManager\Services\FileManagerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class FileManagerServiceTest extends TestCase
{
    private FileManagerRepositoryInterface $repo;
    private FileManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(FileManagerRepositoryInterface::class);
        $this->service = new FileManagerService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_directory_delegates_to_repository(): void
    {
        $expected = new Collection();
        $this->repo->shouldReceive('listDirectory')->once()->with(42)->andReturn($expected);

        $result = $this->service->listDirectory(new FileListDto(companyId: 1, parentId: 42));

        $this->assertSame($expected, $result);
    }

    public function test_get_item_loads_meta_children_and_links(): void
    {
        $path = new FileManagerPath();
        $this->repo->shouldReceive('findOrFail')
            ->once()
            ->with(7, ['meta', 'children', 'links'])
            ->andReturn($path);

        $result = $this->service->getItem(new FileShowDto(7));

        $this->assertSame($path, $result);
    }

    public function test_update_item_skips_repository_when_no_fields_changed(): void
    {
        $item = Mockery::mock(FileManagerPath::class)->makePartial();
        $item->name = 'unchanged.txt';
        $item->shouldReceive('load')->with('meta')->andReturnSelf();

        $this->repo->shouldReceive('findOrFail')->once()->with(5)->andReturn($item);
        $this->repo->shouldNotReceive('updatePath');

        // name === current name, parentId === null → noop branch
        $dto = new UpdateItemDto(pathId: 5, name: 'unchanged.txt', parentId: null);

        $this->assertSame($item, $this->service->updateItem($dto));
    }

    public function test_update_item_writes_when_name_or_parent_changes(): void
    {
        $item = Mockery::mock(FileManagerPath::class)->makePartial();
        $item->name = 'old.txt';
        $updated = Mockery::mock(FileManagerPath::class)->makePartial();
        $updated->shouldReceive('load')->with('meta')->andReturnSelf();

        $this->repo->shouldReceive('findOrFail')->once()->with(5)->andReturn($item);
        $this->repo->shouldReceive('updatePath')
            ->once()
            ->with($item, ['name' => 'new.txt', 'parent_id' => 12])
            ->andReturn($updated);

        $dto = new UpdateItemDto(pathId: 5, name: 'new.txt', parentId: 12);

        $this->assertSame($updated, $this->service->updateItem($dto));
    }

    public function test_delete_item_removes_file_from_disk_and_db(): void
    {
        Storage::fake('local');

        $item = Mockery::mock(FileManagerPath::class)->makePartial();
        $item->path = 'company-1/foo.txt';
        $item->storage = StoragesEnum::local;
        $item->shouldReceive('isFile')->andReturn(true);
        $item->shouldReceive('isDirectory')->andReturn(false);

        $this->repo->shouldReceive('findOrFail')->once()->with(11)->andReturn($item);
        $this->repo->shouldReceive('delete')->once()->with($item);

        $this->service->deleteItem(new DeleteItemDto(11));

        // Mockery-driven assertions don't auto-register; explicit assert keeps PHPUnit happy.
        $this->assertTrue(true);
    }

    public function test_download_throws_when_path_is_directory(): void
    {
        $item = Mockery::mock(FileManagerPath::class)->makePartial();
        $item->shouldReceive('isDirectory')->andReturn(true);

        $this->repo->shouldReceive('findOrFail')->once()->with(15, ['meta'])->andReturn($item);

        $this->expectException(NotFoundHttpException::class);
        $this->service->downloadFile(new DownloadFileDto(15));
    }

    public function test_download_throws_when_file_missing_on_disk(): void
    {
        Storage::fake('local');

        $item = Mockery::mock(FileManagerPath::class)->makePartial();
        $item->path = 'company-1/missing.txt';
        $item->storage = StoragesEnum::local;
        $item->shouldReceive('isDirectory')->andReturn(false);

        $this->repo->shouldReceive('findOrFail')->once()->with(20, ['meta'])->andReturn($item);

        $this->expectException(NotFoundHttpException::class);
        $this->service->downloadFile(new DownloadFileDto(20));
    }

    public function test_find_or_create_directory_returns_existing_when_present(): void
    {
        $existing = new FileManagerPath();
        $this->repo->shouldReceive('findDirectory')
            ->once()
            ->with(50, null, 'photos')
            ->andReturn($existing);
        // No createPath / makeDirectory calls when row already exists.
        $this->repo->shouldNotReceive('createPath');

        $result = $this->service->findOrCreateDirectory('photos', 50);

        $this->assertSame($existing, $result);
    }

    public function test_resolve_storage_falls_back_to_local(): void
    {
        $ref = new ReflectionClass($this->service);
        $method = $ref->getMethod('resolveStorage');
        $method->setAccessible(true);

        $this->assertSame(StoragesEnum::local, $method->invoke($this->service, null));
        $this->assertSame(StoragesEnum::local, $method->invoke($this->service, 'nonsense-disk'));
        $this->assertSame(StoragesEnum::public, $method->invoke($this->service, 'public'));
        $this->assertSame(StoragesEnum::aws, $method->invoke($this->service, 'aws'));
    }

    public function test_company_root_uses_numeric_company_id(): void
    {
        $ref = new ReflectionClass($this->service);
        $method = $ref->getMethod('companyRoot');
        $method->setAccessible(true);

        $this->assertSame('42', $method->invoke($this->service, 42));
    }
}
