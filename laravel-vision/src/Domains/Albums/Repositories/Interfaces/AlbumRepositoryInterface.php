<?php

namespace Albums\Repositories\Interfaces;

use Albums\Models\Album;
use Illuminate\Database\Eloquent\Collection;

/**
 * Albums repository contract.
 */
interface AlbumRepositoryInterface
{
    /**
     * All albums of the current company sorted by date desc.
     *
     * @return Collection<int, Album>
     */
    public function all(): Collection;

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
    public function findOrFail(int $id): Album;

    /**
     * Returns an existing album for camera+date or creates one (unique key: camera_id + date).
     *
     * @param array<string, mixed> $data
     * @return Album
     */
    public function firstOrCreate(array $data): Album;

    /**
     * Updates album columns (e.g. photos_count, folder_name).
     *
     * @param Album $album
     * @param array<string, mixed> $data
     * @return Album
     */
    public function update(Album $album, array $data): Album;

    /**
     * Deletes the album row (no soft delete — albums are removed together with their files).
     *
     * @param Album $album
     * @return void
     */
    public function delete(Album $album): void;

    /**
     * Albums older than the given threshold (used by the retention command).
     *
     * @param \DateTimeInterface $before
     * @return Collection<int, Album>
     */
    public function olderThan(\DateTimeInterface $before): Collection;
}
