<?php

namespace Push\Models;

use Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Web push subscription entry — stores VAPID keys required to send push messages to a device.
 */
class PushSubscription extends Model
{
    /**
     * @var string Table name.
     */
    protected $table = 'vision_push_subscriptions';

    /**
     * @var array<int, string> Mass assignable columns.
     */
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'user_agent',
    ];

    /**
     * The user this subscription belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
