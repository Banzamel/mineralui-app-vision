<?php

namespace Auth\Repositories\Interfaces;

use Illuminate\Support\Collection;
use Laravel\Passport\Token;

/**
 * Passport token repository contract — wraps oauth_access_tokens / oauth_refresh_tokens access
 * so services never touch Passport's Eloquent models or DB::table() directly.
 */
interface TokenRepositoryInterface
{
    /**
     * Returns all currently active (non-revoked, non-expired) tokens for a set of users,
     * ordered by most recently updated first.
     *
     * @param array<int, int> $userIds user id set
     * @return Collection<int, Token> active Passport tokens
     */
    public function activeForUsers(array $userIds): Collection;

    /**
     * Finds a Passport token by id and ensures it belongs to the given user.
     *
     * @param string $tokenId token id (uuid-like string)
     * @param int $userId expected owner user id
     * @return Token|null token when found and owned, otherwise null
     */
    public function findForUser(string $tokenId, int $userId): ?Token;

    /**
     * Revokes the access token together with all refresh tokens that point to it.
     *
     * @param Token $token access token to revoke
     * @return void
     */
    public function revokeWithRefreshTokens(Token $token): void;

    /**
     * Revokes every access token owned by the given user together with their refresh tokens.
     *
     * @param int $userId user whose tokens are being revoked
     * @return void
     */
    public function revokeAllForUser(int $userId): void;
}
