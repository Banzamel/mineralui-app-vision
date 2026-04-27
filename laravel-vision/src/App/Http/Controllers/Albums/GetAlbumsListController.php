<?php

namespace App\Http\Controllers\Albums;

use Albums\Resources\AlbumResource;
use Albums\Services\Interfaces\AlbumServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /vision/albums — list of albums (optionally filtered by camera_id).
 */
readonly class GetAlbumsListController
{
    /**
     * @param AlbumServiceInterface $service Album service.
     */
    public function __construct(private AlbumServiceInterface $service)
    {
    }

    /**
     * @param Request $request Request with optional camera_id.
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $cameraId = $request->integer('camera_id') ?: null;
        $albums = $cameraId
            ? $this->service->byCamera($cameraId)
            : $this->service->list();

        return AlbumResource::collection($albums)->response();
    }
}
