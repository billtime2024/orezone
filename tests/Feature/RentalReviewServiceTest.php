<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\User;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Services\Rental\BookingService;
use App\Services\Rental\ListingService;
use App\Services\Rental\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReviewService $reviewService;
    private BookingService $bookingService;
    private ListingService $listingService;
    private User $owner;
    private User $guest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewService = app(ReviewService::class);
        $this->bookingService = app(BookingService::class);
        $this->listingService = app(ListingService::class);
        $this->owner = User::factory()->create();
        $this->guest = User::factory()->create();
    }

    public function test_create_review_for_completed_booking(): void
    {
        $booking = $this->createCompletedBooking();

        $this->actingAs($this->guest);
        $review = $this->reviewService->createReview($booking, [
            'rating' => 5,
            'comment' => 'Amazing place!',
            'ratings' => [
                'cleanliness' => 5,
                'location' => 4,
                'value' => 5,
            ],
        ]);

        $this->assertNotNull($review->id);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Amazing place!', $review->comment);
        $this->assertEquals(5, $review->ratings['cleanliness']);
    }

    public function test_cannot_review_non_completed_booking(): void
    {
        $booking = $this->createBookingWithStatus('confirmed');

        $this->actingAs($this->guest);
        $this->expectException(BookingException::class);
        $this->reviewService->createReview($booking, [
            'rating' => 5,
            'comment' => 'Great!',
        ]);
    }

    public function test_cannot_review_twice(): void
    {
        $booking = $this->createCompletedBooking();

        $this->actingAs($this->guest);
        $this->reviewService->createReview($booking, [
            'rating' => 5,
            'comment' => 'First review',
        ]);

        $this->expectException(BookingException::class);
        $this->reviewService->createReview($booking, [
            'rating' => 4,
            'comment' => 'Second review',
        ]);
    }

    public function test_only_guest_can_review(): void
    {
        $booking = $this->createCompletedBooking();

        $this->actingAs($this->owner);
        $this->expectException(BookingException::class);
        $this->reviewService->createReview($booking, [
            'rating' => 5,
            'comment' => 'Self review',
        ]);
    }

    public function test_get_listing_reviews(): void
    {
        $listing = $this->createActiveListing();
        $booking = $this->createCompletedBookingForListing($listing);

        $this->actingAs($this->guest);
        $this->reviewService->createReview($booking, [
            'rating' => 5,
            'comment' => 'Excellent!',
        ]);

        $reviews = $this->reviewService->getListingReviews($listing);
        $this->assertEquals(1, $reviews->total());
    }

    private function createActiveListing(): RentalListing
    {
        return $this->listingService->createListing([
            'user_id' => $this->owner->id,
            'rental_type' => 'house',
            'title' => 'Test Listing',
            'price_per_unit' => 5000,
            'price_unit' => 'day',
            'address_line1' => '123 Test Road',
            'city' => 'Goa',
            'state' => 'Goa',
            'pincode' => '403001',
            'status' => 'active',
        ], [
            'bedrooms' => 2,
            'bathrooms' => 1,
            'max_guests' => 4,
        ]);
    }

    private function createBookingWithStatus(string $status): RentalBooking
    {
        $listing = $this->createActiveListing();
        $this->actingAs($this->guest);

        $booking = $this->bookingService->createBooking($listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        // Manually set status for testing (bypass state machine)
        $booking->update(['status' => $status]);
        return $booking->fresh();
    }

    private function createCompletedBooking(): RentalBooking
    {
        return $this->createBookingWithStatus('completed');
    }

    private function createCompletedBookingForListing(RentalListing $listing): RentalBooking
    {
        $this->actingAs($this->guest);

        $booking = $this->bookingService->createBooking($listing, [
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 1,
        ]);

        $booking->update(['status' => 'completed']);
        return $booking->fresh();
    }
}
