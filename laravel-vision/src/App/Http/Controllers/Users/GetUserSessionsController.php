<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\CompanySessionsRequest;
use Administration\Services\Interfaces\UserSessionServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/user-sessions — list of active Passport sessions across the tenant.
 */
readonly class GetUserSessionsController
{
    /**
     * @param UserSessionServiceInterface $sessionService session management service
     */
    public function __construct(private UserSessionServiceInterface $sessionService)
    {
    }

    /**
     * @param CompanySessionsRequest $request validated sessions-list request scoped to the current tenant
     * @return JsonResponse { data: active sessions }
     */
    public function __invoke(CompanySessionsRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->sessionService->listCompanySessions($request->getDto())]);
    }
}
