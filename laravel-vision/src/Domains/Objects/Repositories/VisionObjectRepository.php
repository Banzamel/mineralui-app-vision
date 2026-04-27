<?php

namespace Objects\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\VisionObject;
use Objects\Repositories\Interfaces\VisionObjectRepositoryInterface;

/**
 * Eloquent implementation of the Vision objects repository.
 * Company-level filtering comes from the BelongsToCompany global scope.
 */
class VisionObjectRepository implements VisionObjectRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return VisionObject::query()
            ->orderBy('depth')
            ->orderBy('name')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): VisionObject
    {
        return VisionObject::query()->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): VisionObject
    {
        return VisionObject::query()->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(VisionObject $object, array $data): VisionObject
    {
        $object->update($data);
        return $object->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(VisionObject $object): void
    {
        // Hard delete — per open-questions §9 (analogicznie do CameraRepository::delete()).
        $object->forceDelete();
    }

    /**
     * @inheritDoc
     */
    public function allOrderedWithCameras(?array $cameraColumns = null): Collection
    {
        $query = VisionObject::query();

        if ($cameraColumns !== null) {
            $query->with(['cameras' => fn ($q) => $q->select($cameraColumns)]);
        } else {
            $query->with('cameras');
        }

        return $query->orderBy('depth')->orderBy('name')->get();
    }

    /**
     * @inheritDoc
     */
    public function slugExists(string $slug): bool
    {
        return VisionObject::query()->where('slug', $slug)->exists();
    }

    /**
     * @inheritDoc
     */
    public function depthOf(int $parentId): ?int
    {
        $parent = VisionObject::query()->find($parentId);
        return $parent ? (int) $parent->depth : null;
    }
}
