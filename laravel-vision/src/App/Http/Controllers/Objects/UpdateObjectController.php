<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Dtos\UpdateObjectDto;
use Objects\Requests\UpdateObjectRequest;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * PATCH /vision/objects/{id} — updates an object in the tree.
 */
readonly class UpdateObjectController
{
    /**
     * @param VisionObjectServiceInterface $service Object service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @param UpdateObjectRequest $request Validated input.
     * @param int $id Object ID.
     * @return JsonResponse
     */
    public function __invoke(UpdateObjectRequest $request, int $id): JsonResponse
    {
        $dto = new UpdateObjectDto(
            parentId: $request->has('parent_id') ? $request->input('parent_id') : null,
            name: $request->input('name'),
            type: $request->input('type'),
            address: $request->input('address'),
            description: $request->input('description'),
            mainPhotoPath: $request->input('main_photo_path'),
        );

        $object = $this->service->update($id, $dto);
        return (new VisionObjectResource($object))->response();
    }
}
