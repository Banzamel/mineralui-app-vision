<?php

namespace App\Http\Controllers\Albums;

use Albums\Resources\AlbumResource;
use Albums\Services\Interfaces\AlbumServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /vision/albums/{id} — album details with the list of photos.
 */
readonly class GetAlbumController
{
    /**
     * @param AlbumServiceInterface $service Album service.
     */
    public function __construct(private AlbumServiceInterface $service)
    {
    }

    /**
     * @param int $id Album ID.
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        $album = $this->service->find($id);
        return response()->json(new AlbumResource($album));
    }
}
