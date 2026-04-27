<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserUpdateRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * PUT /administration/users/{user} — updates an existing user's data.
 */
readonly class UpdateUserController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserUpdateRequest $request validated update payload
     * @param int $userId route user id
     * @return JsonResponse user data after the update
     */
    public function __invoke(UserUpdateRequest $request, int $userId): JsonResponse
    {
        $user = $this->userService->update($userId, $request->getDto(), $request->user());
        return response()->json(['data' => $user]);
    }
}
