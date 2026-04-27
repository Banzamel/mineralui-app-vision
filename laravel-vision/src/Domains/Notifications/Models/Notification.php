<?php

namespace Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Traits\BelongsToCompany;

/**
 * Per-user notification entry shown in the notifications drawer. read_at = null means unread.
 */
class Notification extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'vision_notifications';

    /**
     * Mass-assignable columns.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'severity',
        'title',
        'message',
        'data',
        'link',
        'read_at',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        // `data` carries the structured payload the frontend renders via i18n
        // (e.g. {actor_name: 'Anna'}). Cast as array so JSON encoding/decoding is automatic.
        'data' => 'array',
    ];

    /**
     * Notification recipient.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Administration\Models\User::class, 'user_id');
    }
}
