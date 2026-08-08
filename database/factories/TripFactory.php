<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        return [
            'host_id' => User::factory(),
            'vehicle_id' => Vehicle::factory(),
            'origin_name' => $this->faker->city(),
            'origin_lat' => $this->faker->latitude(8, 37),
            'origin_lng' => $this->faker->longitude(68, 97),
            'destination_name' => $this->faker->city(),
            'destination_lat' => $this->faker->latitude(8, 37),
            'destination_lng' => $this->faker->longitude(68, 97),
            'departure_at' => now()->addDays(2),
            'total_seats' => $totalSeats = $this->faker->numberBetween(1, 6),
            'available_seats' => $this->faker->numberBetween(0, $totalSeats),
            'booking_mode' => Trip::BOOKING_MODE_INSTANT,
            'status' => Trip::STATUS_DRAFT,
        ];
    }
}
