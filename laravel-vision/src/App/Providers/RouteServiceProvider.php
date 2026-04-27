<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Route provider — configures the controller namespace and the API rate limiter.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * @var string Namespace applied to controller routes (root namespace for the URL generator).
     */
    protected $namespace = 'Src\Http\Controllers';

    /**
     * Configure the API rate limit: 300 attempts within a 15-minute window per user or IP.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        RateLimiter::for('api', function ($request) {
            return Limit::perMinutes(15, 300)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
