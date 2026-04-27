<?php

namespace App\Http\Controllers\Auth;

use Auth\Services\Interfaces\SocialAuthServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller redirecting the user to the social provider's login page.
 */
readonly class SocialRedirectController
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
     * Redirects the user to the social provider's authorization page.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @return RedirectResponse Redirect response
     */
    public function __invoke(string $provider): RedirectResponse
    {
        return $this->socialAuthService->redirect($provider);
    }
}
