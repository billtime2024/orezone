<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Models\RentalReview;
use App\Services\Rental\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService,
    ) {}

    /**
     * GET /rentals/{listing}/reviews — Get reviews for a listing.
     */
    public function index(Request $request, RentalListing $listing): JsonResponse
    {
        $reviews = $this->reviewService->getListingReviews(
            $listing,
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * POST /rentals-bookings/{booking}/review — Create a review.
     */
    public function store(Request $request, RentalBooking $booking): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'ratings' => 'nullable|array',
            'ratings.cleanliness' => 'nullable|integer|min:1|max:5',
            'ratings.location' => 'nullable|integer|min:1|max:5',
            'ratings.value' => 'nullable|integer|min:1|max:5',
            'ratings.communication' => 'nullable|integer|min:1|max:5',
        ]);

        try {
            $review = $this->reviewService->createReview($booking, $validated);

            return response()->json([
                'success' => true,
                'data' => $review->load('user:id,name,avatar_path'),
                'message' => 'Review submitted successfully.',
            ], 201);
        } catch (BookingException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /admin/rentals-reviews — All reviews (admin).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $reviews = $this->reviewService->getAllReviews(
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * PATCH /admin/rentals-reviews/{review}/toggle — Toggle visibility (admin).
     */
    public function toggleVisibility(Request $request, RentalReview $review): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $review = $this->reviewService->toggleVisibility($review);

        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review visibility toggled.',
        ]);
    }
}
