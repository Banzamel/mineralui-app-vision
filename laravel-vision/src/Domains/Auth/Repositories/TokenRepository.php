<?php

namespace Auth\Repositories;

use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;

/**
 * Eloquent/DB implementation of the Passport token repository.
 */
class TokenRepository implements TokenRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function activeForUsers(array $userIds): Collection
    {
        if (empty($userIds)) {
            return new Collection();
        }

        return Token::query()
            ->whereIn('user_id', $userIds)
            ->where('revoked', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findForUser(string $tokenId, int $userId): ?Token
    {
        return Token::query()
            ->where('id', $tokenId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function revokeWithRefreshTokens(Token $token): void
    {
        $token->revoke();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $token->id)
            ->update(['revoked' => true]);
    }

    /**
     * @inheritDoc
     */
    public function revokeAllForUser(int $userId): void
    {
        DB::table('oauth_access_tokens')
            ->where('user_id', $userId)
            ->update(['revoked' => true]);

        DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', function ($q) use ($userId) {
                $q->select('id')->from('oauth_access_tokens')->where('user_id', $userId);
            })
            ->update(['revoked' => true]);
    }
}
