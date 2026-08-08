<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBookingRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Models\Trip;
use App\Services\RideSharing\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    /**
     * POST /trips/{trip}/bookings — Create a booking.
     * instant → confirmed, request_approval → requested.
     */
    public function store(StoreBookingRequest $request, Trip $trip): JsonResponse
    {
        $this->authorize('create', [Booking::class, $trip]);

        try {
            $booking = $this->bookingService->createBooking(
                $trip,
                $request->user(),
                $request->validated()
            );

            $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

            return response()->json([
                'message' => 'Booking created successfully.',
                'data' => new BookingResource($booking),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /bookings — Traveler's bookings.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Booking::class);

        $query = $request->user()->bookings()
            ->with(['trip.host', 'trip.vehicle', 'pickupStop', 'dropStop']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }

    /**
     * GET /bookings/{booking} — Booking details.
     */
    public function show(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        $booking->load(['trip.host', 'trip.vehicle.category', 'trip.stops', 'traveler', 'pickupStop', 'dropStop']);

        return new BookingResource($booking);
    }

    /**
     * POST /bookings/{booking}/accept — Host accepts booking, decrement seats.
     */
    public function accept(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('accept', $booking);

        try {
            $booking = $this->bookingService->acceptBooking($booking, $request->user());

            $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

            return response()->json([
                'message' => 'Booking accepted successfully.',
                'data' => new BookingResource($booking),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /bookings/{booking}/reject — Host rejects booking.
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('reject', $booking);

        try {
            $booking = $this->bookingService->rejectBooking($booking, $request->user());

            $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

            return response()->json([
                'message' => 'Booking rejected successfully.',
                'data' => new BookingResource($booking),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /bookings/{booking}/cancel — Cancel + restore seats if confirmed.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $booking = $this->bookingService->cancelBooking($booking, $request->user());

            $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

            return response()->json([
                'message' => 'Booking cancelled successfully.',
                'data' => new BookingResource($booking),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /bookings/{booking}/complete — Host marks booking complete.
     */
    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('complete', $booking);

        try {
            $booking = $this->bookingService->completeBooking($booking, $request->user());

            $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

            return response()->json([
                'message' => 'Booking marked as completed.',
                'data' => new BookingResource($booking),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
