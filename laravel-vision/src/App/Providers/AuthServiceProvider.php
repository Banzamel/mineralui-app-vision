<?php

namespace App\Providers;

use Laravel\Passport\Passport;

/**
 * Authorization Service Provider — wires models to policies and configures Laravel Passport.
 * Also defines the available token scopes (web/mobile/api) and their lifetimes.
 */
class AuthServiceProvider extends \Illuminate\Foundation\Support\Providers\AuthServiceProvider
{
    /**
     * @var array<class-string, class-string> Map: model => policy.
     */
    protected $policies = [
        \Objects\Models\Camera::class => \Objects\Policies\CameraScopePolicy::class,
    ];

    /**
     * Configure the User morph map and enable the Passport password grant.
     *
     * @return void
     */
    public function boot(): void
    {
        // Consistent morph map for the User (used across multiple domains).
        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'user' => \Auth\Models\User::class,
        ]);

        Passport::enablePasswordGrant();

        Passport::tokensCan([
            'mobile' => 'Access from mobile application',
            'web' => 'Access from web application',
            'api' => 'Access from external API',
        ]);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
