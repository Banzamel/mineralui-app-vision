<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserAvatarRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /administration/users/{user}/avatar — replaces the user's profile picture.
 */
readonly class UpdateUserAvatarController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserAvatarRequest $request validated avatar file upload
     * @param int $user route user id
     * @return JsonResponse user data after the avatar change
     */
    public function __invoke(UserAvatarRequest $request, int $user): JsonResponse
    {
        $updatedUser = $this->userService->updateAvatar($user, $request->file('avatar'), $request->user());
        return response()->json(['data' => $updatedUser]);
    }
}
