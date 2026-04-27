<?php

namespace Objects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Visibility record — a single resource that a given user has "in their scope".
 * Used by CameraScopePolicy to filter lists and authorize detail access.
 */
class UserScope extends Model
{
    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'vision_user_scopes';

    /**
     * Mass-assignable columns.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'scope_id',
    ];

    /**
     * Relation to the user this scope belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Auth\Models\User::class, 'user_id');
    }
}
