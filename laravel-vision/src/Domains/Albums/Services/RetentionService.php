<?php

namespace Albums\Services;

use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\Interfaces\RetentionServiceInterface;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Dtos\FileShowDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;

/**
 * Retention service — deletes albums older than the configured period along with their files.
 * Used by the scheduled job (scheduler).
 */
class RetentionService implements RetentionServiceInterface
{
    /**
     * @param AlbumRepositoryInterface $albums Albums repository.
     * @param PhotoRepositoryInterface $photos Photos repository.
     * @param FileManagerServiceInterface $fileManager FileManager directory service.
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
    public function purge(int $days): int
    {
        $before = Carbon::today()->subDays($days);
        $old = $this->albums->olderThan($before);
        $removed = 0;

        foreach ($old as $album) {
            DB::transaction(function () use ($album) {
                $this->photos->deleteByAlbum($album->id);
                $this->deleteAlbumDirectory($album);
                $album->file_manager_path_id = null;
                $this->albums->delete($album);
            });
            $removed++;
        }

        return $removed;
    }

    /**
     * Deletes the album directory using FileManager when possible, then falls back to the camera
     * root path on disk so sync cannot recreate the album from a stale leftover folder.
     *
     * @param Album $album Album being purged.
     * @return void
     */
    protected function deleteAlbumDirectory(Album $album): void
    {
        if ($album->file_manager_path_id) {
            try {
                $this->fileManager->deleteItem(new DeleteItemDto((int) $album->file_manager_path_id));
                return;
            } catch (\Throwable) {
                // Fall through to the path-based cleanup below.
            }
        }

        if (! $album->camera_id || ! $album->folder_name) {
            return;
        }

        try {
            $camera = $this->cameras->findOrFail((int) $album->camera_id);
            if (! $camera->file_manager_path_id) {
                return;
            }

            $cameraRoot = $this->fileManager->getItem(new FileShowDto((int) $camera->file_manager_path_id));
            Storage::disk($cameraRoot->storage->value)->deleteDirectory($cameraRoot->path . '/' . $album->folder_name);
        } catch (\Throwable) {
            // The DB rows should still be removed even if storage cleanup fails.
        }
    }
}
