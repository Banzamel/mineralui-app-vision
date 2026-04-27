<?php

namespace App\Http\Controllers\Manage;

use Administration\Requests\UpdateMeRequest;
use Illuminate\Http\JsonResponse;

/**
 * PUT /manage/me — updates the currently logged-in user's profile fields (name/email/password).
 * Implementation is a stub: request validation is wired up, persistence to follow via UserManagementService.
 */
readonly class UpdateMeController
{
    /**
     * @param UpdateMeRequest $request validated profile update request
     * @return JsonResponse confirmation payload
     */
    public function __invoke(UpdateMeRequest $request): JsonResponse
    {
        // TODO: wire up UserManagementService::update($request->user()->id, UserUpdateDto from validated())
        return response()->json(['ok' => true]);
    }
}
