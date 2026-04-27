<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Broadcast provider — registration point for broadcast channels (currently empty, channels live in routes/channels.php).
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Boot the broadcast configuration (currently no additional logic).
     */
    public function boot(): void
    {
    }
}
