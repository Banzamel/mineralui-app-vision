<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\ResetUserPasswordRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /administration/users/{user}/reset-password — rotates the password, revokes active
 * sessions and queues an email with the new temporary password. Response intentionally does
 * NOT carry the password — email is the single source of truth so it never lives in HTTP logs
 * or browser history.
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
     * @return JsonResponse { ok: true } — the password is in the email, never in this response
     */
    public function __invoke(ResetUserPasswordRequest $request): JsonResponse
    {
        $this->userService->resetPassword($request->getDto(), $request->user());
        return response()->json(['ok' => true]);
    }
}
