<?php

namespace Objects\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Objects\Models\UserScope;
use Objects\Repositories\Interfaces\UserScopeRepositoryInterface;
use Objects\Services\Interfaces\UserScopeServiceInterface;

/**
 * Service for bulk-setting visibility (user_scopes).
 * Uses a "delete + insert" strategy inside a transaction.
 */
class UserScopeService implements UserScopeServiceInterface
{
    /**
     * @param UserScopeRepositoryInterface $repository Scopes repository.
     */
    public function __construct(
        protected UserScopeRepositoryInterface $repository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function forUser(int $userId): Collection
    {
        return $this->repository->forUser($userId);
    }

    /**
     * @inheritDoc
     */
    public function sync(int $userId, array $scopes): Collection
    {
        return DB::transaction(function () use ($userId, $scopes) {
            $this->repository->deleteAllForUser($userId);

            foreach ($scopes as $scope) {
                $this->repository->create([
                    'user_id' => $userId,
                    'type' => $scope['type'],
                    'scope_id' => (string) $scope['scope_id'],
                ]);
            }

            return $this->repository->forUser($userId);
        });
    }
}
