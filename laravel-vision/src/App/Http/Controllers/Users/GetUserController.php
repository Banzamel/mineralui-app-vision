<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserShowRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/users/{user} — returns user details by identifier.
 */
readonly class GetUserController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserShowRequest $request validated show request carrying the route user id
     * @return JsonResponse user data
     */
    public function __invoke(UserShowRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->userService->show($request->getDto())]);
    }
}
