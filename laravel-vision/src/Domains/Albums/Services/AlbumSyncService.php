<?php

namespace Albums\Services;

use Albums\Enums\RetentionPolicyEnum;
use Albums\Events\AlbumCreatedEvent;
use Albums\Events\PhotoAddedEvent;
use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\Interfaces\AlbumSyncServiceInterface;
use FileManager\Dtos\FileShowDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Objects\Models\Camera;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;

/**
 * Service that scans camera directories and synchronizes albums/photos into the database.
 * Daily directory naming scheme: YYYY-MM-DD or DD-MM-YYYY — both supported.
 */
class AlbumSyncService implements AlbumSyncServiceInterface
{
    /**
     * @param AlbumRepositoryInterface $albums albums repository
     * @param PhotoRepositoryInterface $photos photos repository
     * @param FileManagerServiceInterface $fileManager file manager directory service
     * @param CameraRepositoryInterface $cameras cameras repository (tenant iteration without crossing domains via Eloquent)
     */
    public function __construct(
        protected AlbumRepositoryInterface $albums,
        protected PhotoRepositoryInterface $photos,
        protected FileManagerServiceInterface $fileManager,
        protected CameraRepositoryInterface $cameras,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function syncCamera(Camera $camera): int
    {
        if (! $camera->file_manager_path_id) {
            return 0;
        }

        $root = $this->fileManager->getItem(new FileShowDto($camera->file_manager_path_id));
        $storage = $root->storage->value;
        $added = 0;

        $dayDirs = Storage::disk($storage)->directories($root->path);

        foreach ($dayDirs as $dayPath) {
            $dayName = basename($dayPath);
            $date = $this->parseDate($dayName);
            if ($date === null) {
                continue;
            }
            if ($this->isExpiredDate($date)) {
                continue;
            }

            $album = $this->albums->firstOrCreate([
                'company_id' => $camera->company_id,
                'camera_id' => $camera->id,
                'date' => $date,
                'folder_name' => $dayName,
                'photos_count' => 0,
            ]);

            // Materialize the day directory in the FileManager (idempotent — both the disk
            // makeDirectory and the row creation only happen when missing) and bind the path
            // id to the album, so PhotoStreamService can resolve `{folder.path}/{photo.filename}`.
            if (! $album->file_manager_path_id) {
                $dayFolder = $this->fileManager->findOrCreateDirectory(
                    $dayName,
                    $camera->company_id,
                    $camera->file_manager_path_id,
                    $storage,
                );
                $this->albums->update($album, ['file_manager_path_id' => $dayFolder->id]);
                $album->file_manager_path_id = $dayFolder->id;
            }

            if ($album->wasRecentlyCreated) {
                event(new AlbumCreatedEvent($album));
            }

            $added += $this->syncAlbumPhotos($album, $dayPath, $storage);
        }

        return $added;
    }

    /**
     * @inheritDoc
     */
    public function syncAll(): int
    {
        $total = 0;
        foreach ($this->cameras->all() as $camera) {
            $total += $this->syncCamera($camera);
        }
        return $total;
    }

    /**
     * Adds missing photos from the directory to the album.
     *
     * @param Album $album Album to update.
     * @param string $dirPath Directory path on disk.
     * @param string $storage Disk name.
     * @return int Number of new photos added.
     */
    protected function syncAlbumPhotos(Album $album, string $dirPath, string $storage): int
    {
        $disk = Storage::disk($storage);
        $files = $disk->files($dirPath);
        $added = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $mime = $disk->mimeType($filePath) ?? 'image/jpeg';
            if (! str_starts_with($mime, 'image/')) {
                continue;
            }

            $photo = $this->photos->upsertByFilename([
                'album_id' => $album->id,
                'filename' => $filename,
                'bytes' => (int) $disk->size($filePath),
                'mime' => $mime,
                'taken_at' => now()->setTimestamp($disk->lastModified($filePath)),
            ]);

            if ($photo->wasRecentlyCreated) {
                $added++;
                event(new PhotoAddedEvent($photo));
            }
        }

        if ($added > 0) {
            $this->albums->update($album, [
                'photos_count' => $album->photos_count + $added,
            ]);
        }

        return $added;
    }

    /**
     * Parses a daily directory name into a date.
     *
     * Cameras name day folders in many flavours, so we extract the first date-shaped pattern
     * from the name and let Carbon validate it. Supported separators: `-`, `_`, `.`, `/`.
     * Examples that round-trip:
     *  - `2026-04-26`              (ISO)
     *  - `2026_04_26`              (Hikvision-style underscore)
     *  - `2026.04.26`
     *  - `2026_04_26-2026_04_26`   (range — first match wins, single-day captures)
     *  - `26-04-2026`              (legacy DMY)
     *  - `20260426`                (compact, no separators)
     *
     * Carbon validates the extracted triple, so `2026-13-99` (junk that happens to match a
     * regex shape) is rejected — only real calendar dates make it through.
     *
     * @param string $name Directory name.
     * @return string|null Date in YYYY-MM-DD format or null if no valid date is recognised.
     */
    protected function parseDate(string $name): ?string
    {
        // First try YMD with any common separator (anchored anywhere in the string —
        // grabs the start of `2026_04_26-2026_04_26` or a prefix in `2026-04-26_morning`).
        if (preg_match('/(\d{4})[-_.\/](\d{1,2})[-_.\/](\d{1,2})/', $name, $m)) {
            $date = $this->buildDate((int) $m[1], (int) $m[2], (int) $m[3]);
            if ($date !== null) {
                return $date;
            }
        }

        // Legacy DMY layout (older firmware on some cameras).
        if (preg_match('/^(\d{1,2})[-_.\/](\d{1,2})[-_.\/](\d{4})$/', $name, $m)) {
            $date = $this->buildDate((int) $m[3], (int) $m[2], (int) $m[1]);
            if ($date !== null) {
                return $date;
            }
        }

        // Compact `YYYYMMDD` with no separators.
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $name, $m)) {
            $date = $this->buildDate((int) $m[1], (int) $m[2], (int) $m[3]);
            if ($date !== null) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Builds a Y-m-d string and validates it via Carbon — guards against month/day overflow
     * (e.g. 2026-02-30 or 2026-13-01 should not be silently accepted as valid albums).
     *
     * @param int $year four-digit year
     * @param int $month month (1-12)
     * @param int $day day (1-31)
     * @return string|null `YYYY-MM-DD` or null when the triple does not form a real date
     */
    private function buildDate(int $year, int $month, int $day): ?string
    {
        if (!checkdate($month, $day, $year)) {
            return null;
        }
        return Carbon::create($year, $month, $day)->format('Y-m-d');
    }

    /**
     * Stops sync from resurrecting directories that are already older than the retention policy.
     *
     * @param string $date Parsed album date in Y-m-d format.
     * @return bool True when the date is older than the current retention cutoff.
     */
    protected function isExpiredDate(string $date): bool
    {
        $cutoff = Carbon::today()->subDays(RetentionPolicyEnum::DefaultDays->value)->format('Y-m-d');
        return $date < $cutoff;
    }
}
