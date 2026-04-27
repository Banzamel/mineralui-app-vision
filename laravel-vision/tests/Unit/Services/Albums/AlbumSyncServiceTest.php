<?php

namespace Tests\Unit\Services\Albums;

use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\AlbumSyncService;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;
use ReflectionClass;
use Tests\TestCase;

class AlbumSyncServiceTest extends TestCase
{
    private AlbumRepositoryInterface $albums;
    private PhotoRepositoryInterface $photos;
    private FileManagerServiceInterface $fileManager;
    private CameraRepositoryInterface $cameras;
    private AlbumSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->albums = Mockery::mock(AlbumRepositoryInterface::class);
        $this->photos = Mockery::mock(PhotoRepositoryInterface::class);
        $this->fileManager = Mockery::mock(FileManagerServiceInterface::class);
        $this->cameras = Mockery::mock(CameraRepositoryInterface::class);

        $this->service = new AlbumSyncService(
            $this->albums,
            $this->photos,
            $this->fileManager,
            $this->cameras,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_parse_date_accepts_iso_format(): void
    {
        $this->assertSame('2026-04-23', $this->callParseDate('2026-04-23'));
    }

    public function test_parse_date_accepts_underscore_separator(): void
    {
        // Hikvision / Dahua firmware typically writes underscores: `2026_04_26`.
        $this->assertSame('2026-04-26', $this->callParseDate('2026_04_26'));
    }

    public function test_parse_date_accepts_dotted_or_slashed_separators(): void
    {
        $this->assertSame('2026-04-26', $this->callParseDate('2026.04.26'));
        $this->assertSame('2026-04-26', $this->callParseDate('2026/04/26'));
    }

    public function test_parse_date_takes_first_date_in_a_range(): void
    {
        // Cameras that capture full-day windows write folder names like `start-end`. Albums are
        // grouped per day, so the start date wins.
        $this->assertSame('2026-04-26', $this->callParseDate('2026_04_26-2026_04_26'));
        $this->assertSame('2026-04-26', $this->callParseDate('2026-04-26-2026-04-27'));
    }

    public function test_parse_date_accepts_compact_no_separator(): void
    {
        $this->assertSame('2026-04-26', $this->callParseDate('20260426'));
    }

    public function test_parse_date_accepts_legacy_dmy_format(): void
    {
        $this->assertSame('2026-04-23', $this->callParseDate('23-04-2026'));
    }

    public function test_parse_date_rejects_invalid_calendar_dates(): void
    {
        // Regex shape matches but the date is impossible — checkdate() rejects.
        $this->assertNull($this->callParseDate('2026-13-01'));
        $this->assertNull($this->callParseDate('2026-02-30'));
    }

    public function test_parse_date_returns_null_for_unrecognized_names(): void
    {
        $this->assertNull($this->callParseDate('not-a-date'));
        $this->assertNull($this->callParseDate('morning-shift'));
        $this->assertNull($this->callParseDate(''));
    }

    public function test_sync_camera_returns_zero_when_camera_has_no_path_id(): void
    {
        $camera = new \Objects\Models\Camera();
        $camera->file_manager_path_id = null;

        $result = $this->service->syncCamera($camera);

        $this->assertSame(0, $result);
    }

    public function test_sync_all_iterates_cameras_and_sums_counts(): void
    {
        // Two cameras both without path id → both early-return 0; we still verify the loop iterates.
        $cameraA = new \Objects\Models\Camera();
        $cameraA->file_manager_path_id = null;
        $cameraB = new \Objects\Models\Camera();
        $cameraB->file_manager_path_id = null;

        $this->cameras->shouldReceive('all')->once()->andReturn(new Collection([$cameraA, $cameraB]));

        $this->assertSame(0, $this->service->syncAll());
    }

    /**
     * Reflection helper — parseDate() is `protected`; we want to assert its branches without
     * standing up the whole sync pipeline.
     */
    private function callParseDate(string $name): ?string
    {
        $ref = new ReflectionClass($this->service);
        $method = $ref->getMethod('parseDate');
        $method->setAccessible(true);
        return $method->invoke($this->service, $name);
    }
}
