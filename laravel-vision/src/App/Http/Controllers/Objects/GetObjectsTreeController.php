<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\JsonResponse;
use Objects\Resources\VisionObjectResource;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * GET /vision/objects/tree — returns the company's object tree.
 */
readonly class GetObjectsTreeController
{
    /**
     * @param VisionObjectServiceInterface $service Object service.
     */
    public function __construct(private VisionObjectServiceInterface $service)
    {
    }

    /**
     * Returns the list of tree roots with children and cameras eager-loaded.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $tree = $this->service->tree();
        return VisionObjectResource::collection($tree)->response();
    }
}
