<?php

namespace Albums\Services;

use Albums\Models\Photo;
use Albums\Services\Interfaces\ThumbnailServiceInterface;
use FileManager\Dtos\FileShowDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Generates 400x300 JPEG thumbnails (cover-fit, q80) and stores them next to the originals
 * under `{album_path}/thumbs/{filename}.jpg`.
 */
class ThumbnailService implements ThumbnailServiceInterface
{
    private const THUMB_WIDTH = 400;
    private const THUMB_HEIGHT = 300;
    private const THUMB_QUALITY = 80;
    private const THUMBS_DIR = 'thumbs';

    /**
     * @param FileManagerServiceInterface $fileManager FileManager (resolves album folder paths).
     */
    public function __construct(
        protected FileManagerServiceInterface $fileManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function generate(Photo $photo): bool
    {
        $paths = $this->resolvePaths($photo);
        if ($paths === null) {
            return false;
        }

        [$disk, $originalPath, $thumbPath] = $paths;

        if ($disk->exists($thumbPath)) {
            return false;
        }
        if (! $disk->exists($originalPath)) {
            return false;
        }

        $manager = new ImageManager(new GdDriver());
        // decodePath uses GD's imagecreatefromjpeg/png/etc — reliable for JPEGs with large EXIF
        // segments where decodeBinary sometimes trips over the format detection.
        $image = $manager->decodePath($disk->path($originalPath));
        $image->cover(self::THUMB_WIDTH, self::THUMB_HEIGHT);
        $bytes = (string) $image->encodeUsingMediaType('image/jpeg', quality: self::THUMB_QUALITY);

        $disk->put($thumbPath, $bytes);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function stream(Photo $photo): StreamedResponse
    {
        $paths = $this->resolvePaths($photo);
        if ($paths === null) {
            throw new NotFoundHttpException('Brak katalogu dla tego albumu.');
        }

        [$disk, , $thumbPath] = $paths;

        if (! $disk->exists($thumbPath)) {
            // Lazy generate — happens when a viewer hits a photo before the queue worker did.
            $this->generate($photo);
        }

        if (! $disk->exists($thumbPath)) {
            throw new NotFoundHttpException('Plik miniatury nie istnieje na dysku.');
        }

        return $disk->response($thumbPath, basename($thumbPath), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Resolves the storage disk + original/thumb paths for a photo. Returns null when the album
     * has no FileManager directory bound (sync did not run yet).
     *
     * @param Photo $photo Photo entity.
     * @return array{0: \Illuminate\Contracts\Filesystem\Filesystem, 1: string, 2: string}|null
     */
    private function resolvePaths(Photo $photo): ?array
    {
        $album = $photo->album;
        if (! $album || ! $album->file_manager_path_id) {
            return null;
        }

        $folder = $this->fileManager->getItem(new FileShowDto($album->file_manager_path_id));
        $disk = Storage::disk($folder->storage->value);

        $originalPath = $folder->path . '/' . $photo->filename;
        $thumbPath = $folder->path . '/' . self::THUMBS_DIR . '/' . $this->thumbFilename($photo->filename);

        return [$disk, $originalPath, $thumbPath];
    }

    /**
     * Returns the thumbnail filename — same stem as the original, forced .jpg extension.
     *
     * @param string $filename Original filename.
     * @return string Thumb filename (`name.jpg`).
     */
    private function thumbFilename(string $filename): string
    {
        $stem = pathinfo($filename, PATHINFO_FILENAME);
        return $stem . '.jpg';
    }
}
