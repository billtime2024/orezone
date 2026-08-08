<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Sedan', 'SUV', 'Hatchback', 'MPV', 'Luxury']),
            'slug' => $this->faker->unique()->slug(1),
            'is_active' => true,
        ];
    }
}
