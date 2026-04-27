<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserCreateRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /administration/users — creates a new user within the caller's tenant.
 */
readonly class CreateUserController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserCreateRequest $request validated new user data
     * @return JsonResponse created user payload (status 201)
     */
    public function __invoke(UserCreateRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->getDto(), $request->user());
        return response()->json(['data' => $user], 201);
    }
}
