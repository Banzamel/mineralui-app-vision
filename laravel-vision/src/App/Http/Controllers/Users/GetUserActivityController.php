<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\CompanyActivityRequest;
use Administration\Services\Interfaces\UserActivityServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/user-activity — recent activity entries across all users in the tenant.
 */
readonly class GetUserActivityController
{
    /**
     * @param UserActivityServiceInterface $activityService activity history service
     */
    public function __construct(private UserActivityServiceInterface $activityService)
    {
    }

    /**
     * @param CompanyActivityRequest $request validated activity-list request scoped to the current tenant
     * @return JsonResponse { data: activity entries }
     */
    public function __invoke(CompanyActivityRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->activityService->listCompanyActivity($request->getDto())]);
    }
}
