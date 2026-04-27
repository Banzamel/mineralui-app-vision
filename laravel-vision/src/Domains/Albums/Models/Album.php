<?php

namespace Albums\Models;

use Albums\Factories\AlbumFactory;
use Albums\Observers\AlbumObserver;
use FileManager\Models\FileManagerPath;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Objects\Models\Camera;
use Shared\Traits\BelongsToCompany;
use Shared\Traits\Loggable;

/**
 * Album of photos from a single day, for a specific camera.
 * Photos for the day land in the "{companyId}/{...object-tree}/{cameraSlug}/{date}" directory in FileManager.
 */
#[ObservedBy(AlbumObserver::class)]
class Album extends Model
{
    use HasFactory, BelongsToCompany, Loggable;

    /**
     * @var string Database table name.
     */
    protected $table = 'vision_albums';

    /**
     * @var array<int, string> Mass assignable columns.
     */
    protected $fillable = [
        'company_id',
        'camera_id',
        'date',
        'folder_name',
        'file_manager_path_id',
        'photos_count',
    ];

    /**
     * @var array<string, string> Column casts to PHP types.
     */
    protected $casts = [
        'date' => 'date',
        'photos_count' => 'integer',
    ];

    /**
     * Boots the model and points to its own test factory.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        Factory::guessFactoryNamesUsing(fn () => AlbumFactory::class);
    }

    /**
     * The camera this album belongs to.
     *
     * @return BelongsTo
     */
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class, 'camera_id');
    }

    /**
     * FileManager directory corresponding to the album.
     *
     * @return BelongsTo
     */
    public function fileManagerPath(): BelongsTo
    {
        return $this->belongsTo(FileManagerPath::class, 'file_manager_path_id');
    }

    /**
     * List of photos in the album.
     *
     * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'album_id');
    }
}
