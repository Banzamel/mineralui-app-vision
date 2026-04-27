<?php

namespace Objects\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Objects\Factories\VisionObjectFactory;
use Shared\Scopes\HasActiveScope;
use Shared\Traits\BelongsToCompany;
use Shared\Traits\Loggable;

/**
 * A node in the Vision objects tree (building, apartment, garage, etc.).
 * The depth field lets the frontend render indentation without recursive counting.
 * The BelongsToCompany global scope filters rows by the current company.
 */
class VisionObject extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, HasActiveScope, Loggable;

    /**
     * Boots the model and points it to its own test factory.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        Factory::guessFactoryNamesUsing(fn () => VisionObjectFactory::class);
    }

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'vision_objects';

    /**
     * Mass-assignable columns for create/update.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'slug',
        'type',
        'address',
        'description',
        'main_photo_path',
        'depth',
    ];

    /**
     * Relation to the parent — the object sitting higher in the tree.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relation to direct children in the tree.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Cameras attached to this object.
     *
     * @return HasMany
     */
    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class, 'object_id');
    }

    /**
     * Public URL for `main_photo_path` (browser-loadable). Null when no photo is set.
     *
     * @return string|null
     */
    public function getMainPhotoUrlAttribute(): ?string
    {
        return $this->main_photo_path
            ? Storage::disk('public')->url($this->main_photo_path)
            : null;
    }
}
