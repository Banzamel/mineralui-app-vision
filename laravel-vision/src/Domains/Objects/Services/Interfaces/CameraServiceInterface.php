<?php

namespace Objects\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Objects\Dtos\CreateCameraDto;
use Objects\Dtos\UpdateCameraDto;
use Objects\Models\Camera;

/**
 * Cameras service contract — CRUD plus stream password handling.
 */
interface CameraServiceInterface
{
    /**
     * All cameras of the current company.
     *
     * @return Collection<int, Camera>
     */
    public function list(): Collection;

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
    public function find(int $id): Camera;

    /**
     * Creates a new camera, encrypts the stream password and emits CameraCreatedEvent.
     *
     * @param CreateCameraDto $dto
     * @return Camera
     */
    public function create(CreateCameraDto $dto): Camera;

    /**
     * Updates an existing camera. Re-encrypts the password when provided.
     *
     * @param int $id
     * @param UpdateCameraDto $dto
     * @return Camera
     */
    public function update(int $id, UpdateCameraDto $dto): Camera;

    /**
     * Stores the uploaded image on the public disk and points the camera's main_photo_path at it.
     * Replaces (and removes from disk) any previously stored photo.
     *
     * @param int $id Camera id.
     * @param UploadedFile $file Validated image upload.
     * @return Camera Updated camera with `main_photo_url` populated.
     */
    public function updateMainPhoto(int $id, UploadedFile $file): Camera;

    /**
     * Soft-deletes the camera and emits CameraDeletedEvent.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Decrypts and returns the stream password for the camera (or null when not set).
     *
     * @param Camera $camera
     * @return string|null
     */
    public function decryptPassword(Camera $camera): ?string;
}
