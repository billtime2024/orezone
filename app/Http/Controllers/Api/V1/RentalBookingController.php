<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RentalBookingStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Services\Rental\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalBookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {}

    /**
     * POST /rentals/{listing}/bookings — Create a booking.
     */
    public function store(Request $request, RentalListing $listing): JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'guest_message' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|array',
        ]);

        try {
            $booking = $this->bookingService->createBooking($listing, $validated);

            return response()->json([
                'success' => true,
                'data' => $booking->load(['listing:id,title,city,photos,rental_type', 'owner:id,name']),
            ], 201);
        } catch (BookingException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /bookings/my — Guest's bookings.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = RentalBooking::where('user_id', $request->user()->id)
            ->with(['listing:id,title,city,photos,rental_type', 'listing.houseDetails', 'listing.carDetails', 'listing.commercialDetails', 'listing.roomDetails'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * GET /owner/bookings — Host's bookings.
     */
    public function ownerBookings(Request $request): JsonResponse
    {
        $query = RentalBooking::where('owner_id', $request->user()->id)
            ->with(['listing:id,title,city,photos,rental_type', 'guest:id,name,avatar_path,phone']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * GET /bookings/{booking} — Show booking details.
     */
    public function show(Request $request, RentalBooking $booking): JsonResponse
    {
        // Only guest, owner, or admin can view
        $userId = $request->user()->id;
        if ($booking->user_id !== $userId && $booking->owner_id !== $userId && !$request->user()->is_admin) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $booking->load([
            'listing:id,title,city,photos,rental_type,address_line1',
            'listing.details',
            'guest:id,name,avatar_path,phone',
            'owner:id,name,avatar_path,phone',
            'statusHistory' => function ($q) {
                $q->with('changedByUser:id,name')->latest();
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * POST /bookings/{booking}/confirm — Host confirms.
     */
    public function confirm(Request $request, RentalBooking $booking): JsonResponse
    {
        if ($booking->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        try {
            $booking = $this->bookingService->confirmBooking(
                $booking,
                $request->get('host_message')
            );

            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking confirmed.',
            ]);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /bookings/{booking}/reject — Host rejects.
     */
    public function reject(Request $request, RentalBooking $booking): JsonResponse
    {
        if ($booking->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->rejectBooking($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking rejected.',
            ]);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /bookings/{booking}/cancel — Guest cancels.
     */
    public function cancel(Request $request, RentalBooking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->cancelByGuest($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking cancelled.',
            ]);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /bookings/{booking}/host-cancel — Host cancels.
     */
    public function hostCancel(Request $request, RentalBooking $booking): JsonResponse
    {
        if ($booking->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->cancelByHost($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking cancelled by host.',
            ]);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
