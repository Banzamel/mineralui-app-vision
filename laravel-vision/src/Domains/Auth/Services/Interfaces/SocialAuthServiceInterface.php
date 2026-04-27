<?php

namespace Auth\Services\Interfaces;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Social login service contract - defines operations for Google, Facebook etc.
 */
interface SocialAuthServiceInterface
{
    /**
     * Returns a redirect to the login page of the chosen social provider.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @return RedirectResponse Redirect response
     */
    public function redirect(string $provider): RedirectResponse;

    /**
     * Handles the callback from the social provider and returns authorization tokens.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @return array User data along with tokens
     */
    public function callback(string $provider): array;
}
