<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'traveler_id' => User::factory(),
            'host_id' => User::factory(),
            'seat_count' => 1,
            'status' => Booking::STATUS_CONFIRMED,
            'idempotency_key' => Str::uuid()->toString(),
            'requested_at' => now(),
        ];
    }
}
