<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Dtos\CreateObjectDto;
use Objects\Requests\CreateObjectRequest;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * POST /vision/objects — creates a new object in the tree.
 */
readonly class CreateObjectController
{
    /**
     * @param VisionObjectServiceInterface $service Object service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @param CreateObjectRequest $request Validated input.
     * @return JsonResponse
     */
    public function __invoke(CreateObjectRequest $request): JsonResponse
    {
        $dto = new CreateObjectDto(
            parentId: $request->input('parent_id'),
            name: $request->string('name')->toString(),
            type: $request->string('type')->toString(),
            address: $request->input('address'),
            description: $request->input('description'),
            mainPhotoPath: $request->input('main_photo_path'),
        );

        $object = $this->service->create($dto);
        return (new VisionObjectResource($object))->response()->setStatusCode(201);
    }
}
