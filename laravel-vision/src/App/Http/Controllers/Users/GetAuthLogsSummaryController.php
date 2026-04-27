<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\AuthLogsSummaryRequest;
use Administration\Services\Interfaces\UserActivityServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/auth-logs — company-wide login summary (daily counts + totals).
 */
readonly class GetAuthLogsSummaryController
{
    /**
     * @param UserActivityServiceInterface $activityService activity history service
     */
    public function __construct(private UserActivityServiceInterface $activityService)
    {
    }

    /**
     * @param AuthLogsSummaryRequest $request validated summary request scoped to the current tenant
     * @return JsonResponse { data: { daily, totals } }
     */
    public function __invoke(AuthLogsSummaryRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->activityService->authLogsSummary($request->getDto())]);
    }
}
