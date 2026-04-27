<?php

namespace FileManager\Models;

use FileManager\Factories\FileManagerMetaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Traits\GenealogyTrait;

/**
 * File metadata model - stores additional information about a file such as MIME type, extension and checksum.
 */
class FileManagerMeta extends Model
{
    use HasFactory, GenealogyTrait;

    protected $table = 'mgr_file_metas';

    protected $fillable = [
        'path_id',
        'hash',
        'mime_type',
        'extension',
        'metadata',
        'checksum',
    ];

    /**
     * Defines how database fields should be cast to PHP types.
     *
     * @return array Map of fields and their cast types.
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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

        Factory::guessFactoryNamesUsing(fn() => FileManagerMetaFactory::class);
    }

    /**
     * Returns the file entry that this metadata belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relation to the file entry.
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(FileManagerPath::class, 'path_id');
    }
}
