<?php

namespace Database\Factories;

use App\Models\VehicleCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vehicle_category_id' => VehicleCategory::factory(),
            'registration_number' => strtoupper($this->faker->bothify('??-####-??')),
            'brand' => $this->faker->randomElement(['Toyota', 'Honda', 'Hyundai', 'Maruti', 'Tata']),
            'model' => $this->faker->randomElement(['Sedan', 'SUV', 'Hatchback', 'MPV']),
            'year' => $this->faker->numberBetween(2015, 2026),
            'color' => $this->faker->safeColorName(),
            'seating_capacity' => $this->faker->numberBetween(2, 8),
            'verification_status' => 'pending',
            'is_active' => true,
        ];
    }
}
