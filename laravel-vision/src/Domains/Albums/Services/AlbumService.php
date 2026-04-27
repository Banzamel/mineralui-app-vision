<?php

namespace Albums\Services;

use Albums\Dtos\PhotoListDto;
use Albums\Events\AlbumDeletedEvent;
use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Albums\Services\Interfaces\AlbumServiceInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Albums service — listing, fetching and deleting albums on behalf of the frontend.
 */
class AlbumService implements AlbumServiceInterface
{
    /**
     * @param AlbumRepositoryInterface $repository Albums repository.
     * @param PhotoRepositoryInterface $photos Photos repository (for cascading photo removal).
     */
    public function __construct(
        protected AlbumRepositoryInterface $repository,
        protected PhotoRepositoryInterface $photos,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function list(): Collection
    {
        return $this->repository->all();
    }

    /**
     * @inheritDoc
     */
    public function byCamera(int $cameraId): Collection
    {
        return $this->repository->byCamera($cameraId);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): Album
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $album = $this->repository->findOrFail($id);
            $companyId = (int) $album->company_id;
            $cameraId = (int) $album->camera_id;

            $this->photos->deleteByAlbum($album->id);
            $this->repository->delete($album);

            event(new AlbumDeletedEvent($album->id, $companyId, $cameraId));
        });
    }

    /**
     * @inheritDoc
     */
    public function listPhotos(PhotoListDto $dto): CursorPaginator
    {
        return $this->photos->cursorByAlbum(
            albumId: $dto->getAlbumId(),
            perPage: $dto->getPerPage(),
            cursor: $dto->getCursor(),
        );
    }
}
