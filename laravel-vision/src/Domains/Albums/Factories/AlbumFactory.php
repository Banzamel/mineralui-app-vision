<?php

namespace Albums\Factories;

use Albums\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating test albums.
 */
class AlbumFactory extends Factory
{
    /**
     * @var string Model class.
     */
    protected $model = Album::class;

    /**
     * Default values for a test album.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->date();

        return [
            'company_id' => 1,
            'camera_id' => 1,
            'date' => $date,
            'folder_name' => $date,
            'file_manager_path_id' => null,
            'photos_count' => 0,
        ];
    }
}
