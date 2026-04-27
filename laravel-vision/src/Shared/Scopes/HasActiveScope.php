<?php

namespace Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait adding scopes to the model that allow easy fetching of only active or only inactive records.
 */
trait HasActiveScope
{
    /**
     * Narrows the query to records marked as active (is_active = true).
     *
     * @param Builder $query Query builder.
     * @return Builder Builder with the active condition appended.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->getTable() . '.is_active', true);
    }

    /**
     * Narrows the query to records marked as inactive (is_active = false).
     *
     * @param Builder $query Query builder.
     * @return Builder Builder with the inactive condition appended.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where($this->getTable() . '.is_active', false);
    }
}
