<?php

namespace Auth\Services\Interfaces;

use Auth\Dtos\LoginDto;
use Auth\Dtos\RefreshLoginDto;

/**
 * Authorization service contract - defines login, refresh and logout operations.
 */
interface AuthorizationServiceInterface
{
    /**
     * Logs the user in and returns access token data.
     *
     * @param \Auth\Dtos\LoginDto $loginDto Login data (email and password)
     * @param string $client Passport client name
     * @return array User data along with tokens
     */
    public function login(LoginDto $loginDto, string $client): array;

    /**
     * Refreshes the access token based on the refresh token.
     *
     * @param \Auth\Dtos\RefreshLoginDto $refreshDto Data containing the refresh token
     * @param string $client Passport client name
     * @return array Newly issued access token data
     */
    public function refresh(RefreshLoginDto $refreshDto, string $client): array;

    /**
     * Logs the current user out and revokes their access token.
     *
     * @param \Illuminate\Http\Request $request Current HTTP request
     * @return array Logout confirmation message
     */
    public function logout(\Illuminate\Http\Request $request): array;
}
