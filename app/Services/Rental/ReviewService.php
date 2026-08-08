<?php

namespace App\Services\Rental;

use App\Exceptions\BookingException;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Models\RentalReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Create a review for a completed booking.
     */
    public function createReview(RentalBooking $booking, array $data): RentalReview
    {
        // Only completed bookings can be reviewed
        if ($booking->status !== 'completed') {
            throw new BookingException('Can only review completed bookings.');
        }

        // Only the guest can review
        if ($booking->user_id !== Auth::id()) {
            throw new BookingException('Only the guest can review this booking.');
        }

        // Check if already reviewed
        $existing = RentalReview::where('rental_booking_id', $booking->id)->first();
        if ($existing) {
            throw new BookingException('You have already reviewed this booking.');
        }

        return DB::transaction(function () use ($booking, $data) {
            $review = RentalReview::create([
                'rental_listing_id' => $booking->rental_listing_id,
                'rental_booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'ratings' => $data['ratings'] ?? null,
            ]);

            // Update listing aggregate stats
            $this->updateListingStats($booking->rental_listing_id);

            return $review;
        });
    }

    /**
     * Get reviews for a listing.
     */
    public function getListingReviews(RentalListing $listing, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return RentalReview::where('rental_listing_id', $listing->id)
            ->where('is_visible', true)
            ->with('user:id,name,avatar')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all reviews (admin).
     */
    public function getAllReviews(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return RentalReview::with([
            'user:id,name,avatar',
            'listing:id,title,rental_type',
            'booking:id,check_in,check_out',
        ])
        ->orderByDesc('created_at')
        ->paginate($perPage);
    }

    /**
     * Toggle review visibility (admin).
     */
    public function toggleVisibility(RentalReview $review): RentalReview
    {
        $review->update(['is_visible' => !$review->is_visible]);
        $this->updateListingStats($review->rental_listing_id);
        return $review;
    }

    /**
     * Update listing's average rating and review count.
     */
    private function updateListingStats(int $listingId): void
    {
        $stats = RentalReview::where('rental_listing_id', $listingId)
            ->where('is_visible', true)
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as avg_rating')
            ->first();

        RentalListing::where('id', $listingId)->update([
            'avg_rating' => round($stats->avg_rating ?? 0, 2),
            'review_count' => $stats->review_count ?? 0,
        ]);
    }
}
