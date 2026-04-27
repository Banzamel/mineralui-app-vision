<?php

namespace FileManager\Models;

use FileManager\Enums\EntityTypeEnum;
use FileManager\Enums\StoragesEnum;
use FileManager\Factories\FileManagerPathFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Shared\Traits\BelongsToCompany;
use Shared\Traits\GenealogyTrait;

/**
 * File or directory path model - the main entry representing every item in the company file manager.
 */
class FileManagerPath extends Model
{
    use HasFactory, GenealogyTrait, BelongsToCompany;

    protected $table = 'mgr_file_paths';

    protected $fillable = [
        'company_id',
        'hash',
        'parent_id',
        'owner_type',
        'owner_id',
        'type',
        'storage',
        'name',
        'path',
        'size',
    ];

    /**
     * Defines how database fields should be cast to PHP types (enums and numbers).
     *
     * @return array Map of fields and their cast types.
     */
    protected function casts(): array
    {
        return [
            'type' => EntityTypeEnum::class,
            'storage' => StoragesEnum::class,
            'size' => 'integer',
        ];
    }

    /**
     * Registers a custom model factory during boot.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        Factory::guessFactoryNamesUsing(fn() => FileManagerPathFactory::class);
    }

    /**
     * Returns the parent directory of this entry.
     *
     * @return BelongsTo Relation to the parent (or null if it sits in the company root).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Returns the list of entries located directly inside this directory.
     *
     * @return HasMany Relation to children (files and subdirectories).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Returns the metadata record associated with this file (MIME type, extension, checksum).
     *
     * @return HasOne Relation to metadata.
     */
    public function meta(): HasOne
    {
        return $this->hasOne(FileManagerMeta::class, 'path_id');
    }

    /**
     * Returns all links in which this file is used elsewhere in the system.
     *
     * @return HasMany Relation to file links.
     */
    public function links(): HasMany
    {
        return $this->hasMany(FileManagerLink::class, 'path_id');
    }

    /**
     * Returns the object the file or directory is attached to (polymorphic relation).
     *
     * @return MorphTo Path owner.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Checks whether this entry is a directory.
     *
     * @return bool True if the entry is a directory, false otherwise.
     */
    public function isDirectory(): bool
    {
        return $this->type === EntityTypeEnum::dir;
    }

    /**
     * Checks whether this entry is a file.
     *
     * @return bool True if the entry is a file, false otherwise.
     */
    public function isFile(): bool
    {
        return $this->type === EntityTypeEnum::file;
    }
}
