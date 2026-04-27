<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * GET /vision/objects — flat list of all objects.
 */
readonly class GetObjectsListController
{
    /**
     * @param VisionObjectServiceInterface $service Object service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return VisionObjectResource::collection($this->service->list())->response();
    }
}
