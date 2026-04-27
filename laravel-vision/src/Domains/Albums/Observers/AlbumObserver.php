<?php

namespace Albums\Observers;

use Albums\Models\Album;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;

/**
 * Album observer — when an album is deleted (e.g. by retention), cleans up its directory.
 */
class AlbumObserver
{
    /**
     * @param FileManagerServiceInterface $fileManager FileManager service.
     */
    public function __construct(
        protected FileManagerServiceInterface $fileManager,
    ) {
    }

    /**
     * After an album is deleted, removes its FileManager directory along with its files.
     *
     * @param Album $album Deleted album.
     * @return void
     */
    public function deleted(Album $album): void
    {
        if ($album->file_manager_path_id) {
            try {
                $this->fileManager->deleteItem(new DeleteItemDto($album->file_manager_path_id));
            } catch (\Throwable) {
                // the directory may have already disappeared — ignore so we don't blow up the transaction
            }
        }
    }
}
