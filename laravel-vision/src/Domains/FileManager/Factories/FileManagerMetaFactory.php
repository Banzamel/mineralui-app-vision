<?php

namespace FileManager\Factories;

use FileManager\Models\FileManagerMeta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Test factory for the file metadata model - creates sample metadata entries for testing purposes.
 */
class FileManagerMetaFactory extends Factory
{
    protected $model = FileManagerMeta::class;

    /**
     * Provides default field values for synthetically generated file metadata.
     *
     * @return array<string, mixed> Array of fields populated in the new record.
     */
    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
