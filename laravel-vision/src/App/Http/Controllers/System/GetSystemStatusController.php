<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\JsonResponse;
use System\Requests\SystemStatusRequest;
use System\Resources\SystemStatusResource;
use System\Services\Interfaces\SystemStatusServiceInterface;

/**
 * GET /system/status — disk usage by photos and application version.
 */
readonly class GetSystemStatusController
{
    /**
     * @param SystemStatusServiceInterface $service system status service
     */
    public function __construct(private SystemStatusServiceInterface $service)
    {
    }

    /**
     * @param SystemStatusRequest $request validated status request scoped to the current company
     * @return JsonResponse { data: SystemStatusResource }
     */
    public function __invoke(SystemStatusRequest $request): JsonResponse
    {
        $status = $this->service->current($request->getDto());
        return response()->json(['data' => (new SystemStatusResource($status))->toArray($request)]);
    }
}
