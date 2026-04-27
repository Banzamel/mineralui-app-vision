<?php

namespace App\Http\Controllers\Auth;

use Auth\Requests\LoginRequest;
use Auth\Services\Interfaces\AuthorizationServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Controller handling user login via the API (POST /login endpoint).
 */
readonly class LoginController
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
     * Logs the user in and returns OAuth tokens.
     *
     * @param LoginRequest $request Request with login credentials
     * @return JsonResponse JSON response containing tokens and user data
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->login($request->getDto(), config('passport.clients.desktop'))
        );
    }
}
