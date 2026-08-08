<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\User;
use App\Models\RentalListing;
use App\Services\Rental\BookingService;
use App\Services\Rental\ListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalBookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;
    private ListingService $listingService;
    private User $owner;
    private User $guest;
    private RentalListing $listing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = app(BookingService::class);
        $this->listingService = app(ListingService::class);
        $this->owner = User::factory()->create();
        $this->guest = User::factory()->create();

        $this->listing = $this->listingService->createListing([
            'user_id' => $this->owner->id,
            'rental_type' => 'house',
            'title' => 'Test Beach House',
            'price_per_unit' => 5000,
            'price_unit' => 'day',
            'address_line1' => '123 Beach Road',
            'city' => 'Goa',
            'state' => 'Goa',
            'pincode' => '403001',
            'instant_booking' => true,
            'status' => 'active',
        ], [
            'bedrooms' => 2,
            'bathrooms' => 1,
            'max_guests' => 4,
        ]);

        $this->actingAs($this->guest);
    }

    public function test_create_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 2,
        ]);

        $this->assertNotNull($booking->id);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals(2, $booking->nights);
        $this->assertGreaterThan(0, $booking->total_amount);
    }

    public function test_create_request_booking(): void
    {
        $this->listing->update(['instant_booking' => false]);
        $this->listing->refresh();

        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 2,
        ]);

        $this->assertEquals('pending', $booking->status);
    }

    public function test_cannot_book_own_listing(): void
    {
        $this->actingAs($this->owner);

        $this->expectException(BookingException::class);
        $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);
    }

    public function test_cannot_book_unavailable_dates(): void
    {
        // Create first booking
        $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 2,
        ]);

        // Try overlapping booking
        $this->expectException(BookingException::class);
        $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(4)->format('Y-m-d'),
            'check_out' => now()->addDays(6)->format('Y-m-d'),
            'guests_count' => 1,
        ]);
    }

    public function test_confirm_booking(): void
    {
        $this->listing->update(['instant_booking' => false]);
        $this->listing->refresh();

        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $this->actingAs($this->owner);
        $confirmed = $this->bookingService->confirmBooking($booking, 'See you there!');

        $this->assertEquals('confirmed', $confirmed->status);
        $this->assertEquals('See you there!', $confirmed->host_message);
    }

    public function test_reject_booking(): void
    {
        $this->listing->update(['instant_booking' => false]);
        $this->listing->refresh();

        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $this->actingAs($this->owner);
        $rejected = $this->bookingService->rejectBooking($booking, 'Not available');

        $this->assertEquals('rejected', $rejected->status);
    }

    public function test_guest_cancel_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $cancelled = $this->bookingService->cancelByGuest($booking, 'Changed plans');

        $this->assertEquals('cancelled_by_guest', $cancelled->status);
        $this->assertEquals('guest', $cancelled->cancelled_by);
        $this->assertEquals('Changed plans', $cancelled->cancellation_reason);
    }

    public function test_host_cancel_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $this->actingAs($this->owner);
        $cancelled = $this->bookingService->cancelByHost($booking, 'Emergency maintenance');

        $this->assertEquals('cancelled_by_host', $cancelled->status);
        $this->assertEquals('host', $cancelled->cancelled_by);
    }

    public function test_status_history_recorded(): void
    {
        $booking = $this->bookingService->createBooking($this->listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $this->assertDatabaseHas('rental_booking_status_histories', [
            'rental_booking_id' => $booking->id,
            'to_status' => 'confirmed',
            'actor_type' => 'guest',
        ]);

        $this->actingAs($this->owner);
        $this->bookingService->cancelByHost($booking, 'Emergency');

        $this->assertDatabaseHas('rental_booking_status_histories', [
            'rental_booking_id' => $booking->id,
            'to_status' => 'cancelled_by_host',
            'actor_type' => 'host',
        ]);
    }
}
