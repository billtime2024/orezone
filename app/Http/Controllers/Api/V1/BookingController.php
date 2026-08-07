<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBookingRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * POST /trips/{trip}/bookings — Create a booking.
     * instant → confirmed, request_approval → requested.
     */
    public function store(StoreBookingRequest $request, Trip $trip): JsonResponse
    {
        $user = $request->user();

        // Trip must be published
        if ($trip->status !== 'published') {
            return response()->json([
                'message' => 'This trip is not available for booking.',
            ], 422);
        }

        // Cannot book own trip
        if ($trip->host_id === $user->id) {
            return response()->json([
                'message' => 'You cannot book your own trip.',
            ], 422);
        }

        // Idempotency check
        if ($request->filled('idempotency_key')) {
            $existing = Booking::where('idempotency_key', $request->input('idempotency_key'))->first();
            if ($existing) {
                return response()->json([
                    'message' => 'This booking has already been submitted.',
                    'data' => new BookingResource($existing),
                ], 200);
            }
        }

        $seatCount = $request->validated('seat_count');

        // Validate stops belong to this trip (only if provided)
        if ($request->filled('pickup_stop_id') || $request->filled('drop_stop_id')) {
            $validStopIds = $trip->stops()->pluck('id')->toArray();
            if ($request->filled('pickup_stop_id') && !in_array($request->validated('pickup_stop_id'), $validStopIds)) {
                return response()->json([
                    'message' => 'Selected pickup stop does not belong to this trip.',
                ], 422);
            }
            if ($request->filled('drop_stop_id') && !in_array($request->validated('drop_stop_id'), $validStopIds)) {
                return response()->json([
                    'message' => 'Selected drop-off stop does not belong to this trip.',
                ], 422);
            }
        }

        // Seat reservation inside a transaction with row-level lock
        $booking = DB::transaction(function () use ($trip, $user, $request, $seatCount) {
            $trip = Trip::lockForUpdate()->find($trip->id);

            if ($trip->available_seats < $seatCount) {
                throw new \App\Exceptions\InsufficientSeatsException(
                    'Not enough seats available. Available: ' . $trip->available_seats
                );
            }

            $trip->decrement('available_seats', $seatCount);

            $status = $trip->booking_mode === 'instant' ? 'confirmed' : 'requested';

            return Booking::create([
                'trip_id' => $trip->id,
                'traveler_id' => $user->id,
                'host_id' => $trip->host_id,
                'pickup_stop_id' => $request->validated('pickup_stop_id'),
                'drop_stop_id' => $request->validated('drop_stop_id'),
                'seat_count' => $seatCount,
                'status' => $status,
                'idempotency_key' => $request->input('idempotency_key'),
                'notes' => $request->input('notes'),
            ]);
        });

        $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

        return response()->json([
            'message' => 'Booking created successfully.',
            'data' => new BookingResource($booking),
        ], 201);
    }

    /**
     * GET /bookings — Traveler's bookings.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
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
        if ($booking->traveler_id !== $request->user()->id
            && $booking->trip->host_id !== $request->user()->id) {
            abort(403, 'You do not have access to this booking.');
        }

        $booking->load(['trip.host', 'trip.vehicle.category', 'trip.stops', 'traveler', 'pickupStop', 'dropStop']);

        return new BookingResource($booking);
    }

    /**
     * POST /bookings/{booking}/accept — Host accepts booking, decrement seats.
     */
    public function accept(Request $request, Booking $booking): JsonResponse
    {
        $trip = $booking->trip;

        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'You are not the host of this trip.');
        }

        if (!$booking->canBeAccepted()) {
            return response()->json([
                'message' => 'This booking cannot be accepted in its current status.',
            ], 422);
        }

        DB::transaction(function () use ($booking, $trip) {
            $trip = Trip::lockForUpdate()->find($trip->id);

            if ($trip->available_seats < $booking->seat_count) {
                throw new \App\Exceptions\InsufficientSeatsException(
                    'Not enough seats available to accept this booking.'
                );
            }

            $trip->decrement('available_seats', $booking->seat_count);
            $booking->update(['status' => 'confirmed']);
        });

        $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

        return response()->json([
            'message' => 'Booking accepted successfully.',
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * POST /bookings/{booking}/reject — Host rejects booking.
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $trip = $booking->trip;

        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'You are not the host of this trip.');
        }

        if (!$booking->canBeRejected()) {
            return response()->json([
                'message' => 'This booking cannot be rejected in its current status.',
            ], 422);
        }

        $booking->update(['status' => 'rejected']);

        $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

        return response()->json([
            'message' => 'Booking rejected successfully.',
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * POST /bookings/{booking}/cancel — Cancel + restore seats if confirmed.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        // Either the traveler or the host can cancel
        if ($booking->traveler_id !== $user->id && $booking->trip->host_id !== $user->id) {
            abort(403, 'You do not have permission to cancel this booking.');
        }

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'message' => 'This booking cannot be cancelled in its current status.',
            ], 422);
        }

        $wasConfirmed = $booking->status === 'confirmed';

        DB::transaction(function () use ($booking, $wasConfirmed) {
            $booking->update(['status' => 'cancelled']);

            // Restore seats only if the booking was confirmed
            if ($wasConfirmed) {
                Trip::where('id', $booking->trip_id)
                    ->increment('available_seats', $booking->seat_count);
            }
        });

        $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * POST /bookings/{booking}/complete — Host marks booking complete.
     */
    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $trip = $booking->trip;

        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'You are not the host of this trip.');
        }

        if (!$booking->canBeCompleted()) {
            return response()->json([
                'message' => 'This booking cannot be completed in its current status.',
            ], 422);
        }

        $booking->update(['status' => 'completed']);
        $booking->load(['traveler', 'pickupStop', 'dropStop', 'trip']);

        return response()->json([
            'message' => 'Booking marked as completed.',
            'data' => new BookingResource($booking),
        ]);
    }
}
