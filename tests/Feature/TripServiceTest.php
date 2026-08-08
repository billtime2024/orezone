<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\RideSharing\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripServiceTest extends TestCase
{
    use RefreshDatabase;

    private TripService $service;
    private User $host;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TripService();
        $this->host = User::factory()->create();
        $category = VehicleCategory::create(['name' => 'Sedan', 'slug' => 'sedan']);
        $this->vehicle = Vehicle::factory()->create([
            'user_id' => $this->host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 4,
        ]);
    }

    public function test_create_draft_trip(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
            'booking_mode' => 'instant',
        ]);

        $this->assertEquals('draft', $trip->status);
        $this->assertEquals(3, $trip->available_seats);
        $this->assertEquals($this->host->id, $trip->host_id);
        $this->assertEquals('instant', $trip->booking_mode);
    }

    public function test_create_draft_with_wrong_vehicle(): void
    {
        $otherUser = User::factory()->create();
        $category = VehicleCategory::create(['name' => 'SUV', 'slug' => 'suv']);
        $otherVehicle = Vehicle::factory()->create([
            'user_id' => $otherUser->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 6,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vehicle not found or does not belong to you');

        $this->service->createDraft($this->host, [
            'vehicle_id' => $otherVehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);
    }

    public function test_publish_trip(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);

        $published = $this->service->publishTrip($trip, $this->host);
        $this->assertEquals('published', $published->status);
        $this->assertDatabaseHas('trip_status_history', [
            'trip_id' => $trip->id,
            'status' => 'published',
        ]);
    }

    public function test_publish_trip_by_non_host(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);

        $otherUser = User::factory()->create();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You are not the host');
        $this->service->publishTrip($trip, $otherUser);
    }

    public function test_cannot_publish_trip_without_verified_vehicle(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);
        // Set vehicle verification to pending
        $this->vehicle->update(['verification_status' => 'pending']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vehicle must be verified');
        $this->service->publishTrip($trip, $this->host);
    }

    public function test_cancel_trip(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);
        $this->service->publishTrip($trip, $this->host);

        $cancelled = $this->service->cancelTrip($trip, $this->host);
        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_start_and_complete_trip(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);
        $this->service->publishTrip($trip, $this->host);

        // Simulate all seats booked (required for startTrip)
        $trip->update(['available_seats' => 0]);

        $started = $this->service->startTrip($trip, $this->host);
        $this->assertEquals('in_progress', $started->status);

        $completed = $this->service->completeTrip($trip, $this->host);
        $this->assertEquals('completed', $completed->status);
    }

    public function test_cannot_start_trip_with_available_seats(): void
    {
        $trip = $this->service->createDraft($this->host, [
            'vehicle_id' => $this->vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
        ]);
        $this->service->publishTrip($trip, $this->host);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trip must be fully booked');
        $this->service->startTrip($trip, $this->host);
    }
}
