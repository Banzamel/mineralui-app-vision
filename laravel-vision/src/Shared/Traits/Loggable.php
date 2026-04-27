<?php

namespace Shared\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait adding to a model the automatic recording of changes to the audit log - records creation, update and deletion of records.
 */
trait Loggable
{
    /**
     * Hooks model events - persists an appropriate log entry after every change, also handles soft delete.
     *
     * @return void
     */
    public static function bootLoggable(): void
    {
        static::created(function (Model $model) {
            static::logActivity('created', $model);
        });

        static::updated(function (Model $model) {
            static::logActivity('updated', $model);
        });

        static::deleted(function (Model $model) {
            static::logActivity('deleted', $model);
        });

        if(in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function (Model $model) {
                static::logActivity('restored', $model);
            });

            static::softDeleted(function (Model $model) {
                static::logActivity('softDeleted', $model);
            });
        }
    }

    /**
     * Persists a single activity entry to the audit log - collects changes, user and browser information.
     *
     * @param string $action Type of action performed (e.g. created, updated, deleted).
     * @param Model $model Model on which the action was performed.
     * @return void
     */
    protected static function logActivity(string $action, Model $model): void
    {
        $user = request()->user();

        if (!$user) {
            return;
        }

        if ($action === 'updated')
            $changes = array_diff_assoc($model->getChanges(), $model->getOriginal());
        elseif ($action === 'created')
            $changes = $model->getAttributes();
        else
            $changes = null;

        \Auth\Models\AuthLog::create([
            'action' => $action,
            'model' => get_class($model),
            'table' => $model->getTable(),
            'database' => $model->getConnectionName(),
            'changes' => $changes,
            'row_id' => $model->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);
    }
}
