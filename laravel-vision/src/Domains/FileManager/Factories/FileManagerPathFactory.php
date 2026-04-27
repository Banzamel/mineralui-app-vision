<?php

namespace FileManager\Factories;

use FileManager\Models\FileManagerPath;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Test factory for the file path model - creates sample file and directory entries for testing purposes.
 */
class FileManagerPathFactory extends Factory
{
    protected $model = FileManagerPath::class;

    /**
     * Provides default field values for a synthetically generated file path.
     *
     * @return array<string, mixed> Array of fields populated in the new record.
     */
    public function definition(): array
    {
        return [
            'hash' => $this->faker->unique()->word(),
            'size' => $this->faker->numberBetween(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'created_by' => 0,
            'updated_by' => 0,
        ];
    }
}
