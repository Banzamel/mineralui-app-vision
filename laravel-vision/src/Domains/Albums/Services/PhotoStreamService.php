<?php

namespace Albums\Services;

use Albums\Models\Photo;
use Albums\Services\Interfaces\PhotoStreamServiceInterface;
use FileManager\Dtos\FileShowDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service that streams photos through the backend — no direct storage links are exposed.
 */
class PhotoStreamService implements PhotoStreamServiceInterface
{
    /**
     * @param FileManagerServiceInterface $fileManager FileManager service (to fetch the album path).
     */
    public function __construct(
        protected FileManagerServiceInterface $fileManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function stream(Photo $photo): StreamedResponse
    {
        $album = $photo->album;
        if (! $album || ! $album->file_manager_path_id) {
            throw new NotFoundHttpException('Album folder is not bound.');
        }

        $folder = $this->fileManager->getItem(new FileShowDto($album->file_manager_path_id));
        $disk = Storage::disk($folder->storage->value);
        $filePath = $folder->path . '/' . $photo->filename;

        if (! $disk->exists($filePath)) {
            throw new NotFoundHttpException('Photo file not found on storage.');
        }

        return $disk->response($filePath, $photo->filename, [
            'Content-Type' => $photo->mime ?? 'image/jpeg',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
