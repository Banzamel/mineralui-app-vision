<?php

namespace Albums\Factories;

use Albums\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating test photos.
 */
class PhotoFactory extends Factory
{
    /**
     * @var string Model class.
     */
    protected $model = Photo::class;

    /**
     * Default values for a test photo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => 1,
            'file_manager_meta_id' => null,
            'filename' => $this->faker->uuid() . '.jpg',
            'width' => 1920,
            'height' => 1080,
            'bytes' => $this->faker->numberBetween(50_000, 500_000),
            'mime' => 'image/jpeg',
            'taken_at' => $this->faker->dateTime(),
        ];
    }
}
