<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Objects\Resources\CameraResource;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * GET /vision/cameras — list of cameras (optionally filtered by object_id).
 */
readonly class GetCamerasListController
{
    /**
     * @param CameraServiceInterface $service Camera service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param Request $request Request with optional object_id filter.
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $objectId = $request->integer('object_id') ?: null;
        $cameras = $objectId
            ? $this->service->byObject($objectId)
            : $this->service->list();

        return CameraResource::collection($cameras)->response();
    }
}
