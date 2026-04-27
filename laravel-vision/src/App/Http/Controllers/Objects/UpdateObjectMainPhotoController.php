<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Requests\MainPhotoRequest;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * POST /api/vision/objects/{id}/main-photo — replaces the object's main photo.
 * Accepts multipart/form-data with an `image` field; the previous file is removed from disk.
 */
readonly class UpdateObjectMainPhotoController
{
    /**
     * @param VisionObjectServiceInterface $service Vision objects service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @param MainPhotoRequest $request Validated image upload (max 5 MB).
     * @param int $id Object id.
     * @return JsonResponse Updated object as a Resource.
     */
    public function __invoke(MainPhotoRequest $request, int $id): JsonResponse
    {
        $object = $this->service->updateMainPhoto($id, $request->file('image'));
        return (new VisionObjectResource($object))->response();
    }
}
