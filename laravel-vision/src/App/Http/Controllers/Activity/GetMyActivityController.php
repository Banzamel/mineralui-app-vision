<?php

namespace App\Http\Controllers\Activity;

use Administration\Requests\MyActivityRequest;
use Administration\Services\Interfaces\UserActivityServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /my-activity — activity history of the current user (login/logout, profile/password changes, album actions).
 */
readonly class GetMyActivityController
{
    /**
     * @param UserActivityServiceInterface $activityService activity history service
     */
    public function __construct(private UserActivityServiceInterface $activityService)
    {
    }

    /**
     * @param MyActivityRequest $request validated my-activity request scoped to the current user
     * @return JsonResponse { data: my activity entries }
     */
    public function __invoke(MyActivityRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->activityService->myActivity($request->getDto())]);
    }
}
