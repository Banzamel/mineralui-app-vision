<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Objects\Dtos\CreateCameraDto;
use Objects\Requests\CreateCameraRequest;
use Objects\Resources\CameraResource;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * POST /vision/cameras — creates a new camera under an object.
 */
readonly class CreateCameraController
{
    /**
     * @param CameraServiceInterface $service Camera service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param CreateCameraRequest $request Validated input.
     * @return JsonResponse
     */
    public function __invoke(CreateCameraRequest $request): JsonResponse
    {
        $dto = new CreateCameraDto(
            objectId: (int) $request->input('object_id'),
            name: $request->string('name')->toString(),
            displayName: $request->input('display_name'),
            address: $request->input('address'),
            ip: $request->input('ip'),
            streamUrl: $request->string('stream_url')->toString(),
            streamLogin: $request->input('stream_login'),
            streamPassword: $request->input('stream_password'),
            mainPhotoPath: $request->input('main_photo_path'),
            motionPreviewEnabled: $request->boolean('motion_preview_enabled'),
        );

        $camera = $this->service->create($dto);
        return (new CameraResource($camera))->response()->setStatusCode(201);
    }
}
