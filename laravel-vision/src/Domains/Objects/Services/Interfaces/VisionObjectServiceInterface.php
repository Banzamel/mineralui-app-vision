<?php

namespace Objects\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Objects\Dtos\CreateObjectDto;
use Objects\Dtos\UpdateObjectDto;
use Objects\Models\VisionObject;

/**
 * Vision objects service contract — manages tree nodes (buildings, apartments, etc.) of the company.
 */
interface VisionObjectServiceInterface
{
    /**
     * Returns all objects of the current company as a tree (roots with their children).
     *
     * @return Collection<int, VisionObject>
     */
    public function tree(): Collection;

    /**
     * Flat list of all objects without nesting.
     *
     * @return Collection<int, VisionObject>
     */
    public function list(): Collection;

    /**
     * Returns a single object by id.
     *
     * @param int $id
     * @return VisionObject
     */
    public function find(int $id): VisionObject;

    /**
     * Creates a new tree node and computes its depth.
     *
     * @param CreateObjectDto $dto
     * @return VisionObject
     */
    public function create(CreateObjectDto $dto): VisionObject;

    /**
     * Updates an object and, if parent_id changed, recomputes depth.
     *
     * @param int $id
     * @param UpdateObjectDto $dto
     * @return VisionObject
     */
    public function update(int $id, UpdateObjectDto $dto): VisionObject;

    /**
     * Stores the uploaded image on the public disk and points the object's main_photo_path at it.
     * Replaces (and removes from disk) any previously stored photo.
     *
     * @param int $id Object id.
     * @param UploadedFile $file Validated image upload.
     * @return VisionObject Updated object with `main_photo_url` populated.
     */
    public function updateMainPhoto(int $id, UploadedFile $file): VisionObject;

    /**
     * Soft-deletes the object. Children and cameras remain but lose their parent reference.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Flattened tree for the scope picker (Building → Address → Camera).
     * Buildings = roots (depth=0), addresses = direct children of roots,
     * cameras = all cameras from the subtree of a given address.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildingsForScopePicker(): array;
}
