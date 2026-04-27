<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\SetUserActiveRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * PATCH /administration/users/{user}/active — quick toggle of the account active flag.
 */
readonly class SetUserActiveController
{
    /**
     * @param UserManagementServiceInterface $userService user management service
     */
    public function __construct(private UserManagementServiceInterface $userService)
    {
    }

    /**
     * @param SetUserActiveRequest $request validated toggle request
     * @return JsonResponse { data: updated user }
     */
    public function __invoke(SetUserActiveRequest $request): JsonResponse
    {
        $updated = $this->userService->setActive($request->getDto(), $request->user());
        return response()->json(['data' => $updated]);
    }
}
