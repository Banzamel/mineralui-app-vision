<?php

namespace Albums\Services;

use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\Interfaces\RetentionServiceInterface;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
                if ($album->file_manager_path_id) {
                    $this->fileManager->deleteItem(new DeleteItemDto($album->file_manager_path_id));
                }
                $this->albums->delete($album);
            });
            $removed++;
        }

        return $removed;
    }
}
