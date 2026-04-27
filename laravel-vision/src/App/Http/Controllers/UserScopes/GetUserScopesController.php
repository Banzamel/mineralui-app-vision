<?php

namespace App\Http\Controllers\UserScopes;

use Illuminate\Http\JsonResponse;
use Objects\Resources\UserScopeResource;
use Objects\Services\Interfaces\UserScopeServiceInterface;

/**
 * GET /vision/users/{userId}/scopes — visibility scopes for the given user.
 */
readonly class GetUserScopesController
{
    /**
     * @param UserScopeServiceInterface $service Scope service.
     */
    public function __construct(private UserScopeServiceInterface $service)
    {
    }

    /**
     * @param int $userId User ID.
     * @return JsonResponse
     */
    public function __invoke(int $userId): JsonResponse
    {
        $scopes = $this->service->forUser($userId);
        return UserScopeResource::collection($scopes)->response();
    }
}
