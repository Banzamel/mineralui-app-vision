<?php

namespace FileManager\Factories;

use FileManager\Models\FileManagerLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Test factory for the file link model - creates sample link records for tests and seeds.
 */
class FileManagerLinkFactory extends Factory
{
    protected $model = FileManagerLink::class;

    /**
     * Provides default field values for a synthetically generated file link.
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
