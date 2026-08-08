<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RentalBookingStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRentalBookingRequest;
use App\Http\Resources\Api\V1\RentalBookingResource;
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
    public function store(StoreRentalBookingRequest $request, RentalListing $listing): JsonResponse
    {
        $validated = $request->validated();

        try {
            $booking = $this->bookingService->createBooking($listing, $validated);

            return response()->json([
                'success' => true,
                'data' => new RentalBookingResource(
                    $booking->load(['listing:id,title,city,photos,rental_type', 'owner:id,name'])
                ),
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
            'data' => RentalBookingResource::collection($bookings),
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
            'data' => RentalBookingResource::collection($bookings),
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
    public function show(RentalBooking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

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
            'data' => new RentalBookingResource($booking),
        ]);
    }

    /**
     * POST /bookings/{booking}/confirm — Host confirms.
     */
    public function confirm(RentalBooking $booking): JsonResponse
    {
        $this->authorize('confirm', $booking);

        try {
            $booking = $this->bookingService->confirmBooking($booking);

            return response()->json([
                'success' => true,
                'data' => new RentalBookingResource($booking),
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
        $this->authorize('reject', $booking);

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->rejectBooking($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => new RentalBookingResource($booking),
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
        $this->authorize('cancelByGuest', $booking);

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->cancelByGuest($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => new RentalBookingResource($booking),
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
        $this->authorize('cancelByHost', $booking);

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $booking = $this->bookingService->cancelByHost($booking, $validated['reason']);

            return response()->json([
                'success' => true,
                'data' => new RentalBookingResource($booking),
                'message' => 'Booking cancelled by host.',
            ]);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
