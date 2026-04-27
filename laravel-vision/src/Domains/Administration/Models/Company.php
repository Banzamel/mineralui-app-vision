<?php

namespace Administration\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Administration\Observers\CompanyObserver;
use Shared\Scopes\HasActiveScope;

/**
 * Company (tenant) model representing a record in the sec_companies table.
 * Holds contact data, the active flag and the subscription expiration date.
 */
#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    use SoftDeletes, HasActiveScope;

    protected $table = 'sec_companies';

    /**
     * Whether the model should automatically maintain created_at and updated_at timestamps.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Fields available for mass assignment.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'country',
        'is_active',
    ];

    /**
     * Cast map for company fields.
     *
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime'
    ];

    /**
     * Checks whether the company's subscription has already expired (expiration date in the past).
     *
     * @return bool true when the subscription has expired
     */
    public function isExpired(): bool
    {
        return $this->expired_at !== null && $this->expired_at->isPast();
    }

    /**
     * Returns the relation to all users belonging to this company.
     *
     * @return HasMany one-to-many relation with users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
