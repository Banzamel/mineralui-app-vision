<?php

namespace Objects\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\UserScope;

/**
 * Repository contract for user_scopes — per-user visibility entries.
 */
interface UserScopeRepositoryInterface
{
    /**
     * All scope rows belonging to the user.
     *
     * @param int $userId
     * @return Collection<int, UserScope>
     */
    public function forUser(int $userId): Collection;

    /**
     * Removes every scope row of the given user (used by sync before re-insert).
     *
     * @param int $userId
     * @return void
     */
    public function deleteAllForUser(int $userId): void;

    /**
     * Inserts a new scope row.
     *
     * @param array<string, mixed> $data
     * @return UserScope
     */
    public function create(array $data): UserScope;
}
