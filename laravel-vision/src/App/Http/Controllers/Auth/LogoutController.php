<?php

namespace App\Http\Controllers\Auth;

use Auth\Services\Interfaces\AuthorizationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller handling user logout (POST /logout endpoint).
 */
readonly class LogoutController
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
     * Invalidates the current user's token and logs them out of the system.
     *
     * @param Request $request Current HTTP request
     * @return JsonResponse JSON response with the logout message
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->authService->logout($request));
    }
}
