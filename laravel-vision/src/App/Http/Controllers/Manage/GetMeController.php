<?php

namespace App\Http\Controllers\Manage;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller returning the logged-in user's profile along with roles and permissions.
 */
readonly class GetMeController
{
    /**
     * Returns profile data of the currently logged-in user with roles and permissions.
     *
     * @param Request $request Current HTTP request with the logged-in user data
     * @return JsonResponse JSON response with the profile data
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'company_id' => $user->company_id,
            'is_active' => $user->is_active,
            'avatar_url' => $user->avatar_url,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
