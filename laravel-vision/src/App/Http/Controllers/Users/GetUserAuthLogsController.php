<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserAuthLogsRequest;
use Administration\Services\Interfaces\UserActivityServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/users/{user}/auth-logs — returns the user's authentication log.
 */
readonly class GetUserAuthLogsController
{
    /**
     * @param UserActivityServiceInterface $activityService activity history service
     */
    public function __construct(private UserActivityServiceInterface $activityService)
    {
    }

    /**
     * @param UserAuthLogsRequest $request validated pagination query scoped to the route user
     * @return JsonResponse paginated list of log entries
     */
    public function __invoke(UserAuthLogsRequest $request): JsonResponse
    {
        return response()->json($this->activityService->getUserAuthLogs($request->getDto()));
    }
}
