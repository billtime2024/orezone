<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRatingRequest;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatingController extends Controller
{
    /**
     * POST /ratings — Store a rating/review.
     */
    public function store(StoreRatingRequest $request): JsonResponse
    {
        $user = $request->user();
        $revieweeId = $request->validated('reviewee_id');
        $tripId = $request->validated('trip_id');
        $bookingId = $request->validated('booking_id');

        // Verify user has a completed booking with this reviewee
        $hasCompletedBooking = Booking::where('traveler_id', $user->id)
            ->where(function ($query) use ($revieweeId, $tripId) {
                $query->where('host_id', $revieweeId);
                if ($tripId) {
                    $query->where('trip_id', $tripId);
                }
            })
            ->where('status', 'completed')
            ->exists();

        if (!$hasCompletedBooking) {
            // Also check if user is the host and the reviewee is the traveler
            $hasCompletedBooking = Booking::where('host_id', $user->id)
                ->where('traveler_id', $revieweeId)
                ->where('status', 'completed')
                ->when($tripId, function ($query) use ($tripId) {
                    $query->where('trip_id', $tripId);
                })
                ->exists();
        }

        if (!$hasCompletedBooking) {
            return response()->json([
                'message' => 'You can only rate users you have completed a booking with.',
            ], 422);
        }

        // Check no duplicate review for same trip (DB unique constraint also enforces this)
        if ($tripId) {
            $existingReview = Review::where('reviewer_id', $user->id)
                ->where('reviewee_id', $revieweeId)
                ->where('trip_id', $tripId)
                ->exists();

            if ($existingReview) {
                return response()->json([
                    'message' => 'You have already reviewed this user for this trip.',
                ], 422);
            }
        }

        $review = Review::create([
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'trip_id' => $tripId,
            'booking_id' => $bookingId,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
        ]);

        $review->load(['reviewer', 'reviewee', 'trip', 'booking']);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => new ReviewResource($review),
        ], 201);
    }

    /**
     * GET /users/{user}/ratings — Get user's received ratings with average.
     */
    public function userRatings(Request $request, User $user): AnonymousResourceCollection|JsonResponse
    {
        $reviews = Review::where('reviewee_id', $user->id)
            ->with(['reviewer'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        $avgRating = Review::where('reviewee_id', $user->id)->avg('rating');

        $collection = ReviewResource::collection($reviews);
        $collection->additional([
            'avg_rating' => round((float) $avgRating, 2),
        ]);

        return $collection;
    }
}
