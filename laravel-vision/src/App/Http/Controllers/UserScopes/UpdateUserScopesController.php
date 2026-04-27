<?php

namespace App\Http\Controllers\UserScopes;

use Illuminate\Http\JsonResponse;
use Objects\Requests\UpdateUserScopesRequest;
use Objects\Resources\UserScopeResource;
use Objects\Services\Interfaces\UserScopeServiceInterface;

/**
 * PUT /vision/users/{userId}/scopes — overwrites the entire scope list for the user.
 */
readonly class UpdateUserScopesController
{
    /**
     * @param UserScopeServiceInterface $service Scope service.
     */
    public function __construct(private UserScopeServiceInterface $service)
    {
    }

    /**
     * @param UpdateUserScopesRequest $request Validated input.
     * @param int $userId User ID.
     * @return JsonResponse
     */
    public function __invoke(UpdateUserScopesRequest $request, int $userId): JsonResponse
    {
        $scopes = $this->service->sync($userId, $request->input('scopes', []));
        return UserScopeResource::collection($scopes)->response();
    }
}
