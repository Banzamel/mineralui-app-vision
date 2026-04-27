<?php

namespace App\Http\Controllers\Manage;

use Administration\Requests\UpdateMeRequest;
use Administration\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * PUT /manage/me — updates the currently logged-in user's profile fields (name/email/password).
 * Self-edit, so the actor is the same user being modified. Response shape mirrors GetMeController
 * so the frontend can drop it into the same AuthContext slot without translation.
 */
readonly class UpdateMeController
{
    /**
     * @param UserManagementServiceInterface $users user management service
     */
    public function __construct(private UserManagementServiceInterface $users) {}

    /**
     * @param UpdateMeRequest $request validated profile update request
     * @return JsonResponse fresh user payload after the update
     */
    public function __invoke(UpdateMeRequest $request): JsonResponse
    {
        $actor = $request->user();
        $updated = $this->users->update((int) $actor->id, $request->getDto(), $actor);

        return response()->json([
            'id' => $updated->id,
            'name' => $updated->name,
            'email' => $updated->email,
            'role' => $updated->role ?? null,
            'company_id' => $updated->company_id,
            'is_active' => $updated->is_active,
            'avatar_url' => $updated->avatar_url,
            'roles' => $updated->getRoleNames(),
            'permissions' => $updated->getAllPermissions()->pluck('name'),
        ]);
    }
}
