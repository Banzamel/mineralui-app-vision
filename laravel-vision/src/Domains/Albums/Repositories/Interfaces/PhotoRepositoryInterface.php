<?php

namespace Albums\Repositories\Interfaces;

use Albums\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Photos repository contract.
 */
interface PhotoRepositoryInterface
{
    /**
     * All photos in an album, ordered by taken_at.
     *
     * @param int $albumId
     * @return Collection<int, Photo>
     */
    public function byAlbum(int $albumId): Collection;

    /**
     * Single photo by id or 404.
     *
     * @param int $id
     * @return Photo
     */
    public function findOrFail(int $id): Photo;

    /**
     * Inserts or updates a photo identified by (album_id, filename) — used by the sync job.
     *
     * @param array<string, mixed> $data
     * @return Photo
     */
    public function upsertByFilename(array $data): Photo;

    /**
     * Removes all photos belonging to the given album.
     *
     * @param int $albumId
     * @return void
     */
    public function deleteByAlbum(int $albumId): void;

    /**
     * Sums the byte size of all photos belonging to albums owned by the given company.
     * Used by the System domain to compute storage usage per tenant without leaking Eloquent.
     *
     * @param int $companyId tenant id
     * @return int sum of photo bytes
     */
    public function sumBytesForCompany(int $companyId): int;

    /**
     * Returns a cursor-paginated page of photos in a given album, ordered newest first
     * by taken_at with id as a stable tie-breaker. Uses Laravel's cursorPaginate() so the page
     * is immune to new photos being appended by AlbumSyncService while the user scrolls.
     *
     * @param int $albumId album id
     * @param int $perPage row limit per page
     * @param string|null $cursor opaque cursor from the previous page (null for the first page)
     * @return \Illuminate\Contracts\Pagination\CursorPaginator<Photo> paginator page
     */
    public function cursorByAlbum(int $albumId, int $perPage, ?string $cursor): \Illuminate\Contracts\Pagination\CursorPaginator;
}
