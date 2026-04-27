<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Main application provider — registers global bootstrap mechanisms (e.g. SQL query logging).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register container bindings (currently no custom bindings in this class).
     */
    public function register(): void
    {
    }

    /**
     * Run bootstrap logic: listen to and log every SQL query to the application log.
     */
    public function boot(): void
    {
        DB::listen(function ($query) {
            Log::info('query', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });
    }
}
