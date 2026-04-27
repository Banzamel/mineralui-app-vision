<?php

namespace App\Http\Controllers\Albums;

use Albums\Models\Photo;
use Albums\Services\Interfaces\ThumbnailServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GET /api/vision/photos/{id}/thumb — streams the cached 400x300 JPEG thumbnail.
 * Behind the `signed:relative` middleware (no Bearer auth) so <img> can load it directly;
 * the URL is short-lived and tampering with `id` invalidates the HMAC signature.
 */
readonly class GetPhotoThumbnailController
{
    /**
     * @param ThumbnailServiceInterface $service Thumbnail service.
     */
    public function __construct(private ThumbnailServiceInterface $service)
    {
    }

    /**
     * @param int $id Photo ID.
     * @return StreamedResponse
     */
    public function __invoke(int $id): StreamedResponse
    {
        $photo = Photo::query()->findOrFail($id);
        return $this->service->stream($photo);
    }
}
