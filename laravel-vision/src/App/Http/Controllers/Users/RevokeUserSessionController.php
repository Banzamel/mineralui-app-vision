<?php

namespace App\Http\Controllers\Users;

use Administration\Requests\RevokeSessionRequest;
use Administration\Services\Interfaces\UserSessionServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * DELETE /administration/users/{user}/sessions/{session} — revokes the given OAuth token.
 */
readonly class RevokeUserSessionController
{
    /**
     * @param UserSessionServiceInterface $sessionService session management service
     */
    public function __construct(private UserSessionServiceInterface $sessionService)
    {
    }

    /**
     * @param RevokeSessionRequest $request validated revocation request
     * @return JsonResponse { revoked: true }
     */
    public function __invoke(RevokeSessionRequest $request): JsonResponse
    {
        $this->sessionService->revokeSession($request->getDto());
        return response()->json(['revoked' => true]);
    }
}
