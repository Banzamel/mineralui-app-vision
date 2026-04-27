<?php

namespace Objects\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\VisionObject;

/**
 * Repository contract for the vision_objects table.
 * Company-level filtering is applied via the BelongsToCompany global scope.
 */
interface VisionObjectRepositoryInterface
{
    /**
     * All objects of the company sorted by depth then name (tree order).
     *
     * @return Collection<int, VisionObject>
     */
    public function all(): Collection;

    /**
     * Single object by id or 404.
     *
     * @param int $id
     * @return VisionObject
     */
    public function findOrFail(int $id): VisionObject;

    /**
     * Inserts a new row into vision_objects.
     *
     * @param array<string, mixed> $data
     * @return VisionObject
     */
    public function create(array $data): VisionObject;

    /**
     * Updates an existing object row.
     *
     * @param VisionObject $object
     * @param array<string, mixed> $data
     * @return VisionObject
     */
    public function update(VisionObject $object, array $data): VisionObject;

    /**
     * Soft-deletes the object.
     *
     * @param VisionObject $object
     * @return void
     */
    public function delete(VisionObject $object): void;

    /**
     * Returns all objects of the company with the `cameras` relation eager-loaded,
     * ordered by depth then by name — the shape expected by the tree and scope-picker builders.
     *
     * @param array<int, string>|null $cameraColumns columns to load on the cameras relation (null = all)
     * @return Collection<int, VisionObject> objects with cameras preloaded
     */
    public function allOrderedWithCameras(?array $cameraColumns = null): Collection;

    /**
     * Checks whether any object already uses the given slug (company scope applies via global scope).
     *
     * @param string $slug candidate slug
     * @return bool true when the slug is taken
     */
    public function slugExists(string $slug): bool;

    /**
     * Returns the depth of a parent object or null when it cannot be resolved.
     *
     * @param int $parentId parent object id
     * @return int|null parent depth or null when the parent is missing
     */
    public function depthOf(int $parentId): ?int;
}
