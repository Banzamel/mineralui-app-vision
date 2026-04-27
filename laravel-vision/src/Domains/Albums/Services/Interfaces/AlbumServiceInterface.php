<?php

namespace Albums\Services\Interfaces;

use Albums\Dtos\PhotoListDto;
use Albums\Models\Album;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Albums service contract — listing, fetching and deleting albums on behalf of the frontend.
 */
interface AlbumServiceInterface
{
    /**
     * All albums for the current company.
     *
     * @return Collection<int, Album>
     */
    public function list(): Collection;

    /**
     * Albums of a single camera.
     *
     * @param int $cameraId
     * @return Collection<int, Album>
     */
    public function byCamera(int $cameraId): Collection;

    /**
     * Single album by id with photos eager-loaded.
     *
     * @param int $id
     * @return Album
     */
    public function find(int $id): Album;

    /**
     * Deletes an album together with its photos and FileManager directory and emits AlbumDeletedEvent.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Returns a cursor-paginated page of photos in the given album, newest first.
     *
     * @param PhotoListDto $dto album id + page size + opaque cursor
     * @return CursorPaginator paginator page of Photo models
     */
    public function listPhotos(PhotoListDto $dto): CursorPaginator;
}
