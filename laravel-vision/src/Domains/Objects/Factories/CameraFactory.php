<?php

namespace Objects\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Objects\Models\Camera;

/**
 * Factory for generating test cameras.
 */
class CameraFactory extends Factory
{
    /**
     * @var string Model class handled by this factory.
     */
    protected $model = Camera::class;

    /**
     * Default values for a test camera.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'cam-' . $this->faker->unique()->numberBetween(1, 9999);

        return [
            'company_id' => 1,
            'object_id' => 1,
            'name' => $name,
            'display_name' => 'Kamera ' . $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'address' => $this->faker->address(),
            'ip' => $this->faker->ipv4(),
            'stream_url' => 'rtsp://' . $this->faker->ipv4() . ':554/stream',
            'stream_login' => 'admin',
            'stream_password_encrypted' => Crypt::encryptString('secret'),
            'main_photo_path' => null,
            'file_manager_path_id' => null,
            'is_online' => false,
            'is_active' => true,
            'last_seen_at' => null,
        ];
    }
}
