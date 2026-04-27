<?php

namespace Administration\Observers;

use Administration\Models\User;
use Shared\Exceptions\ApiJsonException;

/**
 * Observer for the User model enforcing business rules during deletion.
 * Reacts to model lifecycle events and blocks disallowed operations.
 */
class UserObserver
{
    /**
     * Blocks deletion of one's own account - a user cannot delete themselves.
     *
     * @param User $user user about to be deleted
     * @return void no return value, throws when the operation is not allowed
     * @throws ApiJsonException when the deletion targets the currently authenticated account
     */
    public function deleting(User $user): void
    {
        if (auth()->check() && $user->id === auth()->id()) {
            throw new ApiJsonException('Cannot delete your own account', 403);
        }
    }
}
