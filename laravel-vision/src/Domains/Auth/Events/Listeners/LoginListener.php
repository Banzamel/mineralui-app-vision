<?php

namespace Auth\Events\Listeners;

/**
 * Listener that records an entry in the auth log when a user logs in.
 */
class LoginListener
{
    /**
     * Handles the login event and adds an entry to the authorization log.
     *
     * @param \Auth\Events\LoginEvent $event User login event
     * @return void
     */
    public function handle(\Auth\Events\LoginEvent $event): void
    {
        \Auth\Models\AuthLog::create([
            'action' => 'login',
            'model' => get_class($event->user),
            'user_id' => $event->user->id,
            'company_id' => $event->user->company_id ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
