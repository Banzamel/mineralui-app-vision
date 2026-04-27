<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * GET /vision/buildings — flattened Building → Address → Camera tree
 * used by the user scope picker and the camera modal.
 */
readonly class GetBuildingsController
{
    /**
     * @param VisionObjectServiceInterface $service Object tree service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => $this->service->buildingsForScopePicker()]);
    }
}
