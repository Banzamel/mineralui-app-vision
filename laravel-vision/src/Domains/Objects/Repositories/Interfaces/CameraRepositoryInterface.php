<?php

namespace Objects\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\Camera;

/**
 * Cameras repository contract.
 */
interface CameraRepositoryInterface
{
    /**
     * All cameras of the current company ordered by name.
     *
     * @return Collection<int, Camera>
     */
    public function all(): Collection;

    /**
     * Cameras attached to a specific parent object.
     *
     * @param int $objectId
     * @return Collection<int, Camera>
     */
    public function byObject(int $objectId): Collection;

    /**
     * Single camera by id or 404.
     *
     * @param int $id
     * @return Camera
     */
    public function findOrFail(int $id): Camera;

    /**
     * Inserts a new camera row.
     *
     * @param array<string, mixed> $data
     * @return Camera
     */
    public function create(array $data): Camera;

    /**
     * Updates an existing camera.
     *
     * @param Camera $camera
     * @param array<string, mixed> $data
     * @return Camera
     */
    public function update(Camera $camera, array $data): Camera;

    /**
     * Soft-deletes the camera.
     *
     * @param Camera $camera
     * @return void
     */
    public function delete(Camera $camera): void;

    /**
     * Checks whether any camera already uses the given slug (company scope applies via global scope).
     *
     * @param string $slug candidate slug
     * @return bool true when the slug is taken
     */
    public function slugExists(string $slug): bool;
}
