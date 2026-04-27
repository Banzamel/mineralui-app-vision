<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * DELETE /vision/objects/{id} — soft-deletes an object.
 */
readonly class DeleteObjectController
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
        $this->service->delete($id);
        return response()->json(['deleted' => true]);
    }
}
