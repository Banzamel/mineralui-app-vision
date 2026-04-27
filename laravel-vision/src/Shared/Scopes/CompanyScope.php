<?php

namespace Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope narrowing queries to data of the logged-in user's company - separates data between companies.
 */
class CompanyScope implements Scope
{
    /**
     * Guard flag preventing infinite recursion of auth()->user() into itself.
     */
    private static bool $resolving = false;

    /**
     * Adds a condition to the query filtering by the logged-in user's company ID.
     *
     * @param Builder $builder Query builder to which the condition is added.
     * @param Model $model Model on which the scope is applied.
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (self::$resolving) {
            return;
        }

        self::$resolving = true;

        try {
            $user = auth()->user();

            if ($user && isset($user->company_id)) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
            }
        } finally {
            self::$resolving = false;
        }
    }
}
