<?php

namespace Objects\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Objects\Enums\ObjectType;
use Objects\Models\VisionObject;

/**
 * Factory for generating test Vision tree objects.
 */
class VisionObjectFactory extends Factory
{
    /**
     * @var string Model class handled by this factory.
     */
    protected $model = VisionObject::class;

    /**
     * Default values for a test object.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->streetName();

        return [
            'company_id' => 1,
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'type' => $this->faker->randomElement(ObjectType::values()),
            'address' => $this->faker->address(),
            'description' => $this->faker->sentence(),
            'main_photo_path' => null,
            'depth' => 0,
            'is_active' => true,
        ];
    }
}
