<?php

namespace Albums\Repositories;

use Albums\Models\Album;
use Albums\Repositories\Interfaces\AlbumRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the albums repository.
 */
class AlbumRepository implements AlbumRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Album::query()->orderByDesc('date')->get();
    }

    /**
     * @inheritDoc
     */
    public function byCamera(int $cameraId): Collection
    {
        return Album::query()
            ->where('camera_id', $cameraId)
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): Album
    {
        return Album::query()->with('photos')->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function firstOrCreate(array $data): Album
    {
        return Album::query()->firstOrCreate(
            [
                'camera_id' => $data['camera_id'],
                'date' => $data['date'],
            ],
            $data,
        );
    }

    /**
     * @inheritDoc
     */
    public function update(Album $album, array $data): Album
    {
        $album->update($data);
        return $album->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(Album $album): void
    {
        $album->delete();
    }

    /**
     * @inheritDoc
     */
    public function olderThan(\DateTimeInterface $before): Collection
    {
        return Album::query()
            ->whereDate('date', '<', $before)
            ->get();
    }
}
