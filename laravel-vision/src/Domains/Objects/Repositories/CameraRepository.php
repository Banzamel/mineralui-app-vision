<?php

namespace Objects\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\Camera;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;

/**
 * Eloquent implementation of the cameras repository.
 */
class CameraRepository implements CameraRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Camera::query()->orderBy('name')->get();
    }

    /**
     * @inheritDoc
     */
    public function byObject(int $objectId): Collection
    {
        return Camera::query()
            ->where('object_id', $objectId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): Camera
    {
        return Camera::query()->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Camera
    {
        return Camera::query()->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(Camera $camera, array $data): Camera
    {
        $camera->update($data);
        return $camera->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(Camera $camera): void
    {
        // Hard delete — per open-questions §9 cameras do not use soft delete (the on-disk
        // folder is removed alongside the record, and slug uniqueness must not collide with
        // a tombstoned deleted_at row). SoftDeletes trait stays on the model for future audit
        // needs but `delete()` always force-deletes.
        $camera->forceDelete();
    }

    /**
     * @inheritDoc
     */
    public function slugExists(string $slug): bool
    {
        // withoutGlobalScopes — check uniqueness within the company even when the auth
        // context is not yet pushed (installer, console commands).
        // The company_id filter is applied explicitly so slugs from other companies do not collide.
        $companyId = (int) (auth()->user()->company_id ?? 0);
        return Camera::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('slug', $slug)
            ->exists();
    }
}
