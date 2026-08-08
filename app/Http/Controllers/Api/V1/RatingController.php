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

        // The submitted booking must exist and link the reviewer to the reviewee
        $booking = Booking::where('id', $bookingId)
            ->where('status', 'completed')
            ->where(function ($query) use ($user, $revieweeId) {
                // Reviewer is the traveler, reviewee is the host
                $query->where('traveler_id', $user->id)
                    ->where('host_id', $revieweeId);
            })
            ->orWhere(function ($query) use ($user, $revieweeId) {
                // Reviewer is the host, reviewee is the traveler
                $query->where('host_id', $user->id)
                    ->where('traveler_id', $revieweeId);
            })
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'The specified booking does not connect you with the reviewee, or is not completed.',
            ], 422);
        }

        // If a trip_id was supplied, it must belong to that booking
        if ($tripId && (int) $booking->trip_id !== (int) $tripId) {
            return response()->json([
                'message' => 'The trip does not belong to the specified booking.',
            ], 422);
        }

        // Use the booking's trip_id as the canonical trip (accepts null from the booking)
        $resolvedTripId = $tripId ?: $booking->trip_id;

        // Check no duplicate review for same booking
        $existingReview = Review::where('reviewer_id', $user->id)
            ->where('reviewee_id', $revieweeId)
            ->where('booking_id', $booking->id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this user for this booking.',
            ], 422);
        }

        $review = Review::create([
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'trip_id' => $resolvedTripId,
            'booking_id' => $booking->id,
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
