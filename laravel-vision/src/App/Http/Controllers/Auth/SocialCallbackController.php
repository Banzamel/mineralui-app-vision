<?php

namespace App\Http\Controllers\Auth;

use Auth\Services\Interfaces\SocialAuthServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Controller handling the callback from a social provider after login.
 */
readonly class SocialCallbackController
{
    /**
     * Initializes the controller with the social auth service.
     *
     * @param SocialAuthServiceInterface $socialAuthService Social authentication service
     */
    public function __construct(private SocialAuthServiceInterface $socialAuthService)
    {
    }

    /**
     * Handles the callback from a social provider and returns authentication data.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @return JsonResponse JSON response with the token and user data
     */
    public function __invoke(string $provider): JsonResponse
    {
        return response()->json($this->socialAuthService->callback($provider));
    }
}
