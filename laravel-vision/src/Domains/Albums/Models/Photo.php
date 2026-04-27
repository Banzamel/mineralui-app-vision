<?php

namespace Albums\Models;

use Albums\Factories\PhotoFactory;
use FileManager\Models\FileManagerMeta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single photo within an album (frame captured by a camera).
 * The FileManager meta reference provides the file mime/checksum/size.
 */
class Photo extends Model
{
    use HasFactory;

    /**
     * @var string Table name.
     */
    protected $table = 'vision_photos';

    /**
     * @var array<int, string> Mass assignable columns.
     */
    protected $fillable = [
        'album_id',
        'file_manager_meta_id',
        'filename',
        'width',
        'height',
        'bytes',
        'mime',
        'taken_at',
    ];

    /**
     * @var array<string, string> Column casts.
     */
    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'bytes' => 'integer',
        'taken_at' => 'datetime',
    ];

    /**
     * Boots the model and points to its own test factory.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        Factory::guessFactoryNamesUsing(fn () => PhotoFactory::class);
    }

    /**
     * The album the photo belongs to.
     *
     * @return BelongsTo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    /**
     * FileManager meta row (mime, checksum).
     *
     * @return BelongsTo
     */
    public function fileManagerMeta(): BelongsTo
    {
        return $this->belongsTo(FileManagerMeta::class, 'file_manager_meta_id');
    }
}
