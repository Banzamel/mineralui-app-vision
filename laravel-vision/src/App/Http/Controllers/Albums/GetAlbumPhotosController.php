<?php

namespace App\Http\Controllers\Albums;

use Albums\Requests\PhotoListRequest;
use Albums\Resources\PhotoResource;
use Albums\Services\Interfaces\AlbumServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /vision/albums/{id}/photos — cursor-paginated feed of photos in a given album.
 * Frontend uses MLoadMore to append pages; the cursor is opaque and round-tripped from meta.next_cursor.
 */
readonly class GetAlbumPhotosController
{
    /**
     * @param AlbumServiceInterface $service albums service
     */
    public function __construct(private AlbumServiceInterface $service)
    {
    }

    /**
     * @param PhotoListRequest $request validated list query (album id + limit + cursor)
     * @return JsonResponse Laravel CursorPaginator serialized via PhotoResource::collection()
     */
    public function __invoke(PhotoListRequest $request): JsonResponse
    {
        $page = $this->service->listPhotos($request->getDto());
        return PhotoResource::collection($page)->response();
    }
}
