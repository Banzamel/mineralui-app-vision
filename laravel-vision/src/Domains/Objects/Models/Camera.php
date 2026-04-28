<?php

namespace Objects\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use FileManager\Models\FileManagerPath;
use Objects\Factories\CameraFactory;
use Objects\Observers\CameraObserver;
use Shared\Scopes\HasActiveScope;
use Shared\Traits\BelongsToCompany;
use Shared\Traits\Loggable;

/**
 * A single video camera attached to an object.
 * Stores the stream URL, login and the encrypted password (Laravel Crypt with APP_KEY).
 * CameraObserver wires the camera up with a FileManager directory on create/delete.
 */
#[ObservedBy(CameraObserver::class)]
class Camera extends Model
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
        Factory::guessFactoryNamesUsing(fn () => CameraFactory::class);
    }

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'vision_cameras';

    /**
     * Mass-assignable columns.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'object_id',
        'name',
        'display_name',
        'slug',
        'address',
        'ip',
        'stream_url',
        'stream_login',
        'stream_password_encrypted',
        'main_photo_path',
        'file_manager_path_id',
        'is_online',
        'last_seen_at',
        'motion_preview_enabled',
    ];

    /**
     * Column casts to PHP types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'motion_preview_enabled' => 'boolean',
    ];

    /**
     * Columns hidden from serialization (RTSP password never goes into JSON).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'stream_password_encrypted',
    ];

    /**
     * Parent object — the building/apartment the camera belongs to.
     *
     * @return BelongsTo
     */
    public function object(): BelongsTo
    {
        return $this->belongsTo(VisionObject::class, 'object_id');
    }

    /**
     * FileManager directory that holds photos from this camera.
     *
     * @return BelongsTo
     */
    public function fileManagerPath(): BelongsTo
    {
        return $this->belongsTo(FileManagerPath::class, 'file_manager_path_id');
    }

    /**
     * Photo albums of this camera.
     *
     * @return HasMany
     */
    public function albums(): HasMany
    {
        return $this->hasMany(\Albums\Models\Album::class, 'camera_id');
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
