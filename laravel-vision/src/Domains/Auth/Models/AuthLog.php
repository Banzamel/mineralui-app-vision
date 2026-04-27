<?php

namespace Auth\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Auth\Observers\AuthLogObserver;
use Shared\Traits\BelongsToCompany;

/**
 * Authorization log model - records all user login and logout actions.
 */
#[ObservedBy(AuthLogObserver::class)]
class AuthLog extends Model
{
    use BelongsToCompany;

    protected $table = 'oauth_logs';

    protected $fillable = [
        'action',
        'model',
        'table',
        'database',
        'row_id',
        'changes',
        'ip_address',
        'user_agent',
        'user_id',
        'company_id',
    ];

    /**
     * Defines attribute type casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    /**
     * Returns the relation to the user that owns the log entry.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Administration\Models\User::class, 'user_id');
    }
}
