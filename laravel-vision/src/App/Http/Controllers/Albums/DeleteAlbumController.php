<?php

namespace App\Http\Controllers\Albums;

use Albums\Services\Interfaces\AlbumServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * DELETE /vision/albums/{id} — removes an album together with its photos and FileManager directory.
 */
readonly class DeleteAlbumController
{
    /**
     * @param AlbumServiceInterface $service Albums service.
     */
    public function __construct(private AlbumServiceInterface $service)
    {
    }

    /**
     * @param int $id Album id.
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['deleted' => true]);
    }
}
