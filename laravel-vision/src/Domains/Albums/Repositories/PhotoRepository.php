<?php

namespace Albums\Repositories;

use Albums\Models\Photo;
use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the photos repository.
 */
class PhotoRepository implements PhotoRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function byAlbum(int $albumId): Collection
    {
        return Photo::query()
            ->where('album_id', $albumId)
            ->orderBy('taken_at')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): Photo
    {
        return Photo::query()->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function upsertByFilename(array $data): Photo
    {
        return Photo::query()->updateOrCreate(
            [
                'album_id' => $data['album_id'],
                'filename' => $data['filename'],
            ],
            $data,
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteByAlbum(int $albumId): void
    {
        Photo::query()->where('album_id', $albumId)->delete();
    }

    /**
     * @inheritDoc
     */
    public function sumBytesForCompany(int $companyId): int
    {
        return (int) Photo::query()
            ->whereHas('album', fn ($q) => $q->where('company_id', $companyId))
            ->sum('bytes');
    }

    /**
     * @inheritDoc
     */
    public function cursorByAlbum(int $albumId, int $perPage, ?string $cursor): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        return Photo::query()
            ->where('album_id', $albumId)
            ->orderByDesc('taken_at')
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $perPage, cursor: $cursor);
    }
}
