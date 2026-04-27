<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\UserDeleteRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * DELETE /administration/users/{user} — removes a user from the tenant (soft delete).
 */
readonly class DeleteUserController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param UserDeleteRequest $request validated delete request carrying the route user id
     * @return JsonResponse { deleted: bool } (status 204)
     */
    public function __invoke(UserDeleteRequest $request): JsonResponse
    {
        $deleted = $this->userService->delete($request->getUserId(), $request->user());
        return response()->json(['deleted' => $deleted], 204);
    }
}
