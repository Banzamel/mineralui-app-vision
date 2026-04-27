<?php

namespace Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Social account model linking a user with an external provider (Google, Facebook).
 */
class SocialAccount extends Model
{
    protected $table = 'auth_social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
    ];

    /**
     * Returns the relation to the user that owns this social account.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
