<?php

namespace Objects\Policies;

use Auth\Models\User;
use Objects\Enums\ScopeType;
use Objects\Models\Camera;
use Objects\Models\UserScope;
use Objects\Models\VisionObject;

/**
 * Policy for cameras — checks whether the user has the camera "in their scope".
 * Admin (with the "administrator" role) sees everything. Others only see what they have in user_scopes.
 */
class CameraScopePolicy
{
    /**
     * Admin bypasses filtering — full visibility.
     *
     * @param User $user Logged-in user.
     * @param string $ability Name of the ability being checked.
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }
        return null;
    }

    /**
     * Can browse the camera list — yes, but the service still filters by scope.
     *
     * @param User $user Logged-in user.
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Whether the user can see this specific camera.
     *
     * @param User $user Logged-in user.
     * @param Camera $camera Camera being checked.
     * @return bool
     */
    public function view(User $user, Camera $camera): bool
    {
        return $this->hasCameraInScope($user->id, $camera);
    }

    /**
     * Checks against user_scopes whether the camera is in scope.
     * Scope can be the camera directly, an address, or a whole object from the tree.
     *
     * @param int $userId User ID.
     * @param Camera $camera Camera to check.
     * @return bool
     */
    protected function hasCameraInScope(int $userId, Camera $camera): bool
    {
        $scopes = UserScope::query()->where('user_id', $userId)->get();
        if ($scopes->isEmpty()) {
            return false;
        }

        foreach ($scopes as $scope) {
            if ($this->matches($scope, $camera)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Matches a single scope entry against the camera.
     *
     * @param UserScope $scope Visibility entry.
     * @param Camera $camera Camera.
     * @return bool
     */
    protected function matches(UserScope $scope, Camera $camera): bool
    {
        return match ($scope->type) {
            ScopeType::Camera->value => (int) $scope->scope_id === $camera->id,
            ScopeType::Address->value => $camera->address !== null && $camera->address === $scope->scope_id,
            ScopeType::Building->value => $this->isInBuildingTree((int) $scope->scope_id, $camera->object_id),
            default => false,
        };
    }

    /**
     * Checks whether the camera hangs under the building (directly or via a sub-object).
     *
     * @param int $buildingId Scope root ID.
     * @param int $objectId ID of the object the camera sits under.
     * @return bool
     */
    protected function isInBuildingTree(int $buildingId, int $objectId): bool
    {
        $current = VisionObject::query()->find($objectId);
        while ($current) {
            if ($current->id === $buildingId) {
                return true;
            }
            if ($current->parent_id === null) {
                return false;
            }
            $current = VisionObject::query()->find($current->parent_id);
        }
        return false;
    }
}
