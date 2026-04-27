<?php

namespace Objects\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\UserScope;

/**
 * User access scope service contract — bulk-syncs user_scopes rows in a "delete + insert" transaction.
 */
interface UserScopeServiceInterface
{
    /**
     * All scope rows of the user.
     *
     * @param int $userId
     * @return Collection<int, UserScope>
     */
    public function forUser(int $userId): Collection;

    /**
     * Replaces the user's scope rows with the given list (atomic delete-then-insert).
     *
     * @param int $userId
     * @param array<int, array{type:string, scope_id:int|string}> $scopes
     * @return Collection<int, UserScope>
     */
    public function sync(int $userId, array $scopes): Collection;
}
