<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * GET /vision/objects/{id} — single object with full details.
 */
readonly class GetObjectController
{
    /**
     * @param VisionObjectServiceInterface $service Object service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @param int $id Object ID.
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        $object = $this->service->find($id);
        $object->load(['cameras', 'children']);
        return (new VisionObjectResource($object))->response();
    }
}
