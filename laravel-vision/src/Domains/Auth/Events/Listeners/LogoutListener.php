<?php

namespace Auth\Events\Listeners;

/**
 * Listener that records an entry in the auth log when a user logs out.
 */
class LogoutListener
{
    /**
     * Handles the logout event and adds an entry to the authorization log.
     *
     * @param \Auth\Events\LogoutEvent $event User logout event
     * @return void
     */
    public function handle(\Auth\Events\LogoutEvent $event): void
    {
        \Auth\Models\AuthLog::create([
            'action' => 'logout',
            'model' => get_class($event->user),
            'user_id' => $event->user->id,
            'company_id' => $event->user->company_id ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
