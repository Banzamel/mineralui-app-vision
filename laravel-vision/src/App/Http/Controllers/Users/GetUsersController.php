<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserListRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /administration/users — returns a paginated list of tenant users.
 */
readonly class GetUsersController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserListRequest $request validated listing request with tenant scope and filters
     * @return JsonResponse paginated list of users
     */
    public function __invoke(UserListRequest $request): JsonResponse
    {
        return response()->json($this->userService->list($request->getDto()));
    }
}
