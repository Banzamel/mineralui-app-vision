<?php

namespace Shared\Helpers;

use Illuminate\Support\Collection;

/**
 * Database migration helper - adds common columns used in many tables (e.g. who created and who updated the entry).
 */
class BlueprintHelper
{
    /**
     * Adds created_by and updated_by columns to a migration storing the IDs of users performing changes.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint Migration object to which the columns are appended.
     * @return Collection Collection of created column definitions.
     */
    static function usersStamps(\Illuminate\Database\Schema\Blueprint $blueprint): Collection
    {
        return new Collection([
            $blueprint->unsignedBigInteger('created_by')->nullable(),
            $blueprint->unsignedBigInteger('updated_by')->nullable(),
        ]);
    }
}
