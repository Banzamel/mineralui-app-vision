<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\ResetUserPasswordRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /administration/users/{user}/reset-password — resets the password and revokes active sessions.
 * Response carries the new temporary password to be delivered to the user out-of-band.
 */
readonly class ResetUserPasswordController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param ResetUserPasswordRequest $request validated reset request carrying IP/user-agent
     * @return JsonResponse { data: { temporary_password: string } }
     */
    public function __invoke(ResetUserPasswordRequest $request): JsonResponse
    {
        $temporary = $this->userService->resetPassword($request->getDto(), $request->user());
        return response()->json(['data' => ['temporary_password' => $temporary]]);
    }
}
