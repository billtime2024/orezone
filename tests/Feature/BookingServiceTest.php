<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Trip;
use App\Services\RideSharing\BookingService;
use App\Services\RideSharing\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;
    private TripService $tripService;
    private User $host;
    private User $traveler;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = new BookingService();
        $this->tripService = new TripService();
        $this->host = User::factory()->create();
        $this->traveler = User::factory()->create();
        $category = VehicleCategory::create(['name' => 'Sedan', 'slug' => 'sedan']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 4,
        ]);
        $this->trip = $this->tripService->createDraft($this->host, [
            'vehicle_id' => $vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
            'booking_mode' => 'instant',
        ]);
        $this->tripService->publishTrip($this->trip, $this->host);
        $this->trip->refresh(); // Must refresh after publish to pick up new status
    }

    public function test_create_instant_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->trip, $this->traveler, [
            'seat_count' => 1,
        ]);

        $this->assertEquals('confirmed', $booking->status);
        $this->trip->refresh();
        $this->assertEquals(2, $this->trip->available_seats);
    }

    public function test_create_request_booking(): void
    {
        // Switch trip to request mode
        $this->trip->update(['booking_mode' => 'request']);
        $this->trip->refresh();

        $booking = $this->bookingService->createBooking($this->trip, $this->traveler, [
            'seat_count' => 1,
        ]);

        $this->assertEquals('requested', $booking->status);
        // Seats should NOT be decremented for request mode
        $this->trip->refresh();
        $this->assertEquals(3, $this->trip->available_seats);
    }

    public function test_cannot_book_own_trip(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot book your own trip');
        $this->bookingService->createBooking($this->trip, $this->host, [
            'seat_count' => 1,
        ]);
    }

    public function test_cannot_overbook_seats(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not enough seats');
        $this->bookingService->createBooking($this->trip, $this->traveler, [
            'seat_count' => 10,
        ]);
    }

    public function test_cancel_booking_restores_seats(): void
    {
        $booking = $this->bookingService->createBooking($this->trip, $this->traveler, [
            'seat_count' => 2,
        ]);
        $this->trip->refresh();
        $this->assertEquals(1, $this->trip->available_seats);

        $this->bookingService->cancelBooking($booking, $this->traveler);
        $this->trip->refresh();
        $this->assertEquals(3, $this->trip->available_seats);
    }

    public function test_accept_request_booking_decrements_seats(): void
    {
        // Create a request-mode trip
        $category = VehicleCategory::create(['name' => 'SUV', 'slug' => 'suv']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 4,
        ]);
        $trip = $this->tripService->createDraft($this->host, [
            'vehicle_id' => $vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(3),
            'total_seats' => 2,
            'booking_mode' => 'request',
        ]);
        $this->tripService->publishTrip($trip, $this->host);
        $trip->refresh();

        // Create a request booking
        $booking = $this->bookingService->createBooking($trip, $this->traveler, [
            'seat_count' => 1,
        ]);
        $this->assertEquals('requested', $booking->status);
        $trip->refresh();
        $this->assertEquals(2, $trip->available_seats); // seats not decremented yet

        // Host accepts
        $accepted = $this->bookingService->acceptBooking($booking, $this->host);
        $this->assertEquals('accepted', $accepted->status);
        $trip->refresh();
        $this->assertEquals(1, $trip->available_seats); // seats now decremented
    }

    public function test_reject_request_booking(): void
    {
        $category = VehicleCategory::create(['name' => 'Bus', 'slug' => 'bus']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 6,
        ]);
        $trip = $this->tripService->createDraft($this->host, [
            'vehicle_id' => $vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(3),
            'total_seats' => 4,
            'booking_mode' => 'request',
        ]);
        $this->tripService->publishTrip($trip, $this->host);
        $trip->refresh();

        $booking = $this->bookingService->createBooking($trip, $this->traveler, [
            'seat_count' => 1,
        ]);

        $rejected = $this->bookingService->rejectBooking($booking, $this->host);
        $this->assertEquals('rejected', $rejected->status);
    }

    public function test_cannot_book_unpublished_trip(): void
    {
        // Create a draft trip that was never published
        $category = VehicleCategory::create(['name' => 'Bike', 'slug' => 'bike']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 2,
        ]);
        $draftTrip = $this->tripService->createDraft($this->host, [
            'vehicle_id' => $vehicle->id,
            'origin_name' => 'Mumbai',
            'destination_name' => 'Pune',
            'departure_at' => now()->addDays(1),
            'total_seats' => 2,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trip is not available for booking');
        $this->bookingService->createBooking($draftTrip, $this->traveler, [
            'seat_count' => 1,
        ]);
    }
}
