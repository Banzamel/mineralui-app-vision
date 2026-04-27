<?php

namespace Objects\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Objects\Models\UserScope;
use Objects\Repositories\Interfaces\UserScopeRepositoryInterface;

/**
 * Eloquent implementation of the visibility entries repository.
 */
class UserScopeRepository implements UserScopeRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function forUser(int $userId): Collection
    {
        return UserScope::query()->where('user_id', $userId)->get();
    }

    /**
     * @inheritDoc
     */
    public function deleteAllForUser(int $userId): void
    {
        UserScope::query()->where('user_id', $userId)->delete();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): UserScope
    {
        return UserScope::query()->create($data);
    }
}
