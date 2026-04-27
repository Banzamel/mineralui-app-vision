<?php

namespace FileManager\Models;

use FileManager\Factories\FileManagerLinkFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Shared\Traits\GenealogyTrait;

/**
 * File link model - connects a specific file to any owner in the system (e.g. camera, album).
 */
class FileManagerLink extends Model
{
    use HasFactory, GenealogyTrait;

    protected $table = 'mgr_file_links';

    protected $fillable = [
        'path_id',
        'target_type',
        'target_id',
        'url',
    ];

    /**
     * Registers a custom model factory during boot.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        Factory::guessFactoryNamesUsing(fn() => FileManagerLinkFactory::class);
    }

    /**
     * Returns the file entry this link points to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relation to the file entry.
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(FileManagerPath::class, 'path_id');
    }

    /**
     * Returns the target object the file is linked to (polymorphic relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo Object the file is attached to.
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
