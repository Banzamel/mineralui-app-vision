<?php

namespace App\Http\Controllers\Auth;

use Auth\Requests\RefreshLoginRequest;
use Auth\Services\Interfaces\AuthorizationServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Controller handling OAuth access token refresh (POST /refresh endpoint).
 */
readonly class RefreshTokenController
{
    /**
     * Initializes the controller with the authorization service.
     *
     * @param AuthorizationServiceInterface $authService Authorization service
     */
    public function __construct(private AuthorizationServiceInterface $authService)
    {
    }

    /**
     * Refreshes the OAuth access token based on the supplied refresh token.
     *
     * @param RefreshLoginRequest $request Request with the refresh token
     * @return JsonResponse JSON response with the new tokens
     */
    public function __invoke(RefreshLoginRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->refresh($request->getDto(), config('passport.clients.desktop'))
        );
    }
}
