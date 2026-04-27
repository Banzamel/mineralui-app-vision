<?php

namespace Administration\Services;

use Administration\Dtos\CompanySessionsQueryDto;
use Administration\Dtos\RevokeSessionDto;
use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\Interfaces\UserSessionServiceInterface;
use Auth\Models\AuthLog;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Illuminate\Support\Carbon;
use Laravel\Passport\Token;
use Shared\Exceptions\ApiJsonException;

/**
 * Passport-session management service scoped to a tenant.
 * Sessions = active oauth_access_tokens; this service never touches Eloquent or Passport directly.
 */
class UserSessionService implements UserSessionServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository users repository (tenant membership checks)
     * @param TokenRepositoryInterface $tokenRepository Passport token repository
     * @param AuthLogRepositoryInterface $authLogRepository auth log repository (ip/user-agent decoration)
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected TokenRepositoryInterface $tokenRepository,
        protected AuthLogRepositoryInterface $authLogRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function listCompanySessions(CompanySessionsQueryDto $dto): array
    {
        $userIds = $this->userRepository->idsByCompany($dto->getCompanyId());
        if (empty($userIds)) {
            return [];
        }

        $tokens = $this->tokenRepository->activeForUsers($userIds);
        if ($tokens->isEmpty()) {
            return [];
        }

        $latestLoginByUser = $this->authLogRepository->latestLoginPerUser($userIds);
        $latestActivityByUser = $this->authLogRepository->latestActivityTimestampPerUser($userIds);
        $currentId = $dto->getCurrentTokenId();

        return $tokens->map(function (Token $token) use ($latestLoginByUser, $latestActivityByUser, $currentId) {
            /** @var AuthLog|null $log */
            $log = $latestLoginByUser->get($token->user_id);
            $ua = $log?->user_agent;
            $lastActiveRaw = $latestActivityByUser->get($token->user_id) ?? $token->updated_at;
            $lastActive = $lastActiveRaw ? Carbon::parse($lastActiveRaw) : null;

            return [
                'id' => (string) $token->id,
                'user_id' => (int) $token->user_id,
                'ip' => $log->ip_address ?? '',
                'user_agent' => $ua ?? '',
                'device' => $this->detectDevice($ua),
                'location' => null,
                'last_active_at' => optional($lastActive)->toIso8601String(),
                'created_at' => optional($token->created_at)->toIso8601String(),
                'current' => $currentId !== null && $currentId === (string) $token->id,
            ];
        })->all();
    }

    /**
     * @inheritDoc
     */
    public function revokeSession(RevokeSessionDto $dto): void
    {
        $owner = $this->userRepository->findInCompany($dto->getUserId(), $dto->getCompanyId());
        if (!$owner) {
            throw new ApiJsonException('Użytkownik spoza firmy.', 404);
        }

        $token = $this->tokenRepository->findForUser($dto->getSessionId(), $dto->getUserId());
        if (!$token) {
            throw new ApiJsonException('Sesja nie istnieje.', 404);
        }

        $this->tokenRepository->revokeWithRefreshTokens($token);
    }

    /**
     * Crude device family detection based on the User-Agent string.
     *
     * @param string|null $ua user-agent header value
     * @return string device family label used by the admin sessions panel
     */
    private function detectDevice(?string $ua): string
    {
        if (!$ua) {
            return 'Nieznane';
        }
        $ua = strtolower($ua);
        return match (true) {
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os x') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Nieznane',
        };
    }
}
