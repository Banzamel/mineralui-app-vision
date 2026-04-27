<?php

namespace Auth\Services;

use Auth\Models\SocialAccount;
use Auth\Models\User;
use Auth\Services\Interfaces\SocialAuthServiceInterface;
use Laravel\Passport\Client;
use Laravel\Socialite\Facades\Socialite;
use Shared\Exceptions\ApiJsonException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Social login service - handles authentication via Google and Facebook.
 */
readonly class SocialAuthService implements SocialAuthServiceInterface
{
    /**
     * List of supported social providers.
     */
    private const array SUPPORTED_PROVIDERS = ['google', 'facebook'];

    /**
     * @inheritDoc
     *
     * @throws ApiJsonException When the provider is not supported
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * @inheritDoc
     *
     * @throws ApiJsonException When authentication fails or the account does not exist
     */
    public function callback(string $provider): array
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            throw new ApiJsonException('Failed to authenticate with ' . $provider, 401);
        }

        $user = $this->findUserBySocialAccount($provider, $socialUser->getId())
            ?? $this->findAndLinkUser($provider, $socialUser);

        if (!$user) {
            throw new ApiJsonException('No account found for this email. Contact your administrator.', 404);
        }

        if (!$user->is_active) {
            throw new ApiJsonException('Account is deactivated', 403);
        }

        return $this->issueToken($user);
    }

    /**
     * Looks up a user by their linked social account.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @param string $providerId User identifier at the provider
     * @return User|null Found user or null
     */
    private function findUserBySocialAccount(string $provider, string $providerId): ?User
    {
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        return $socialAccount?->user;
    }

    /**
     * Looks up a user by email and links a social account to them.
     *
     * @param string $provider Provider name (e.g. google, facebook)
     * @param \Laravel\Socialite\Contracts\User $socialUser User data from the provider
     * @return User|null Found and linked user or null if no such user exists
     */
    private function findAndLinkUser(string $provider, \Laravel\Socialite\Contracts\User $socialUser): ?User
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            return null;
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        return $user;
    }

    /**
     * Issues a personal Passport token for the user after a successful social login.
     *
     * @param User $user User for whom the token is being created
     * @return array User data along with the token
     * @throws ApiJsonException
     */
    private function issueToken(User $user): array
    {
        $tokenResult = $user->createToken('Social Login', ['api']);

        event(new \Auth\Events\LoginEvent($user));

        setPermissionsTeamId($user->company_id);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
                'is_active' => $user->is_active,
                'avatar_url' => $user->avatar_url,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Checks whether the given provider is supported by the system.
     *
     * @param string $provider Provider name to check
     * @throws ApiJsonException When the provider is not supported
     */
    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            throw new ApiJsonException('Unsupported social provider: ' . $provider, 422);
        }
    }
}
