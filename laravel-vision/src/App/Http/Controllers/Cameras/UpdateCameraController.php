<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Objects\Dtos\UpdateCameraDto;
use Objects\Requests\UpdateCameraRequest;
use Objects\Resources\CameraResource;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * PATCH /vision/cameras/{id} — updates a camera.
 */
readonly class UpdateCameraController
{
    /**
     * @param CameraServiceInterface $service Camera service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param UpdateCameraRequest $request Validated input.
     * @param int $id Camera ID.
     * @return JsonResponse
     */
    public function __invoke(UpdateCameraRequest $request, int $id): JsonResponse
    {
        $dto = new UpdateCameraDto(
            objectId: $request->input('object_id'),
            name: $request->input('name'),
            displayName: $request->input('display_name'),
            address: $request->input('address'),
            ip: $request->input('ip'),
            streamUrl: $request->input('stream_url'),
            streamLogin: $request->input('stream_login'),
            streamPassword: $request->input('stream_password'),
            mainPhotoPath: $request->input('main_photo_path'),
            motionPreviewEnabled: $request->has('motion_preview_enabled')
                ? $request->boolean('motion_preview_enabled')
                : null,
        );

        $camera = $this->service->update($id, $dto);
        return (new CameraResource($camera))->response();
    }
}
