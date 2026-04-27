<?php

namespace App\Http\Controllers\Albums;

use Albums\Models\Photo;
use Albums\Services\Interfaces\PhotoStreamServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GET /vision/photos/{id}/stream — streams the photo file through the backend.
 */
readonly class GetPhotoStreamController
{
    /**
     * @param PhotoStreamServiceInterface $service Streaming service.
     */
    public function __construct(private PhotoStreamServiceInterface $service)
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
