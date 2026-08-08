<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTripRequest;
use App\Http\Requests\Api\V1\UpdateTripRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Http\Resources\Api\V1\TripResource;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    /**
     * GET /trips/my — User's own trips, filterable by status.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->trips()->with(['vehicle.category', 'stops']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $trips = $query->orderByDesc('departure_at')
            ->paginate($request->integer('per_page', 15));

        return TripResource::collection($trips);
    }

    /**
     * GET /trips/search — Published trips with available seats.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $query = Trip::published()
            ->with(['host', 'vehicle', 'stops'])
            ->where('available_seats', '>', 0);

        if ($request->filled('origin')) {
            $query->where('origin_name', 'like', '%' . $request->input('origin') . '%');
        }

        if ($request->filled('destination')) {
            $query->where('destination_name', 'like', '%' . $request->input('destination') . '%');
        }

        if ($request->filled('departure_date')) {
            $query->whereDate('departure_at', $request->input('departure_date'));
        }

        $trips = $query->orderBy('departure_at')
            ->paginate($request->integer('per_page', 15));

        return TripResource::collection($trips);
    }

    /**
     * POST /trips — Create a draft trip.
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        $user = $request->user();

        // Verify vehicle belongs to user
        $vehicle = Vehicle::where('id', $request->validated('vehicle_id'))
            ->where('user_id', $user->id)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'message' => 'Vehicle does not belong to you.',
            ], 403);
        }

        $trip = Trip::create([
            'host_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'origin_name' => $request->validated('origin_name'),
            'destination_name' => $request->validated('destination_name'),
            'departure_at' => $request->validated('departure_at'),
            'total_seats' => $request->validated('total_seats'),
            'available_seats' => $request->validated('total_seats'),
            'booking_mode' => $request->validated('booking_mode', 'instant'),
            'notes' => $request->validated('notes'),
            'status' => 'draft',
        ]);

        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json(new TripResource($trip), 201);
    }

    /**
     * GET /trips/{trip} — Trip details with stops/host/vehicle.
     */
    public function show(Trip $trip): TripResource
    {
        $trip->load(['host', 'vehicle.category', 'stops', 'bookings']);

        return new TripResource($trip);
    }

    /**
     * PATCH /trips/{trip} — Update draft trip only.
     */
    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (!$trip->canBePublished() && $trip->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft trips can be updated.',
            ], 422);
        }

        $data = $request->validated();

        // Validate vehicle ownership if vehicle_id is being changed
        if (isset($data['vehicle_id'])) {
            $vehicle = Vehicle::where('id', $data['vehicle_id'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehicle does not belong to you.',
                ], 403);
            }
        }

        // Safely recalculate available_seats when total_seats changes
        if (isset($data['total_seats']) && $data['total_seats'] !== $trip->total_seats) {
            $newTotal = $data['total_seats'];
            $bookedSeats = $trip->bookings()
                ->where('status', 'confirmed')
                ->sum('seat_count');

            if ($bookedSeats > $newTotal) {
                return response()->json([
                    'message' => 'Cannot reduce total_seats below the number of already confirmed seats (' . $bookedSeats . ').',
                ], 422);
            }

            $data['available_seats'] = $newTotal - $bookedSeats;
        }

        $trip->update($data);
        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json(new TripResource($trip));
    }

    /**
     * POST /trips/{trip}/publish — Draft → Published.
     */
    public function publish(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (!$trip->canBePublished()) {
            return response()->json([
                'message' => 'Only draft trips can be published.',
            ], 422);
        }

        $trip->update(['status' => 'published']);
        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json([
            'message' => 'Trip published successfully.',
            'data' => new TripResource($trip),
        ]);
    }

    /**
     * POST /trips/{trip}/cancel — Cancel trip + pending bookings.
     */
    public function cancel(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (!$trip->canBeCancelled()) {
            return response()->json([
                'message' => 'This trip cannot be cancelled in its current status.',
            ], 422);
        }

        DB::transaction(function () use ($trip) {
            $trip->update(['status' => 'cancelled']);

            // Cancel all pending/requested bookings — no seat restore (seats reserved on accept only)
            $requestedBookings = $trip->bookings()
                ->where('status', 'requested')
                ->get();

            foreach ($requestedBookings as $booking) {
                $booking->update(['status' => 'cancelled']);
            }

            // Cancel confirmed bookings and restore seats
            $confirmedBookings = $trip->bookings()
                ->where('status', 'confirmed')
                ->get();

            foreach ($confirmedBookings as $booking) {
                $trip->increment('available_seats', $booking->seat_count);
                $booking->update(['status' => 'cancelled']);
            }
        });

        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json([
            'message' => 'Trip cancelled successfully.',
            'data' => new TripResource($trip),
        ]);
    }

    /**
     * POST /trips/{trip}/start — Published/full → In progress.
     */
    public function start(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (!$trip->canBeStarted()) {
            return response()->json([
                'message' => 'Trip can only be started when it is published and fully booked.',
            ], 422);
        }

        $trip->update(['status' => 'in_progress']);
        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json([
            'message' => 'Trip started successfully.',
            'data' => new TripResource($trip),
        ]);
    }

    /**
     * POST /trips/{trip}/complete — In progress → Completed.
     */
    public function complete(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (!$trip->canBeCompleted()) {
            return response()->json([
                'message' => 'Only in-progress trips can be completed.',
            ], 422);
        }

        DB::transaction(function () use ($trip) {
            $trip->update([
                'status' => 'completed',
                'arrival_at' => now(),
            ]);

            // Mark confirmed bookings as completed
            $trip->confirmedBookings()->update(['status' => 'completed']);
        });

        $trip->load(['host', 'vehicle.category', 'stops']);

        return response()->json([
            'message' => 'Trip completed successfully.',
            'data' => new TripResource($trip),
        ]);
    }

    /**
     * GET /trips/{trip}/booking-requests — Trip's bookings.
     */
    public function bookingRequests(Request $request, Trip $trip): AnonymousResourceCollection
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        $bookings = $trip->bookings()
            ->with(['traveler', 'pickupStop', 'dropStop'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }
}
