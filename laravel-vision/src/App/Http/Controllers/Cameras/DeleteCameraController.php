<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * DELETE /vision/cameras/{id} — soft-deletes a camera.
 */
readonly class DeleteCameraController
{
    /**
     * @param CameraServiceInterface $service Camera service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param int $id Camera ID.
     * @return JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['deleted' => true]);
    }
}
