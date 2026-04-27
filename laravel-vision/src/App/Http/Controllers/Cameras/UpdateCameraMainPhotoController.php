<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Objects\Requests\MainPhotoRequest;
use Objects\Resources\CameraResource;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * POST /api/vision/cameras/{id}/main-photo — replaces the camera's main photo.
 * Accepts multipart/form-data with an `image` field; the previous file is removed from disk.
 */
readonly class UpdateCameraMainPhotoController
{
    /**
     * @param CameraServiceInterface $service Cameras service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param MainPhotoRequest $request Validated image upload (max 5 MB).
     * @param int $id Camera id.
     * @return JsonResponse Updated camera as a Resource.
     */
    public function __invoke(MainPhotoRequest $request, int $id): JsonResponse
    {
        $camera = $this->service->updateMainPhoto($id, $request->file('image'));
        return (new CameraResource($camera))->response();
    }
}
