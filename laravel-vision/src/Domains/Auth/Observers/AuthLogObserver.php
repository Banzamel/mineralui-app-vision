<?php

namespace Auth\Observers;

use Auth\Models\AuthLog;

/**
 * Observer for the AuthLog model - fills in default fields before persisting to the database.
 */
class AuthLogObserver
{
    /**
     * Handles the "creating" event and sets the default user and company on the log entry.
     *
     * @param AuthLog $authLog Authorization log entry
     * @return void
     */
    public function creating(AuthLog $authLog): void
    {
        $authLog->user_id = $authLog->user_id ?? request()->user()?->id;
        $authLog->company_id = $authLog->company_id ?? request()->user()?->company_id;
    }
}
