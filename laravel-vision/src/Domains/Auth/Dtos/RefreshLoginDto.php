<?php

namespace Auth\Dtos;

/**
 * Object holding data for refreshing the login token (refresh token).
 */
readonly class RefreshLoginDto
{
    /**
     * Creates a new object with the refresh token.
     *
     * @param string $refreshToken Token used to refresh the user's session
     */
    public function __construct(
        private string $refreshToken
    ) {
    }

    /**
     * Returns the refresh data as an array.
     *
     * @return array Array containing the refresh token
     */
    public function toArray(): array
    {
        return [
            'refresh_token' => $this->refreshToken
        ];
    }

    /**
     * Returns the refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
