<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTripRequest;
use App\Http\Requests\Api\V1\UpdateTripRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Http\Resources\Api\V1\TripResource;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\RideSharing\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TripController extends Controller
{
    public function __construct(
        private readonly TripService $tripService,
    ) {}

    /**
     * GET /trips/my — User's own trips, filterable by status.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Trip::class);

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
        $filters = array_filter([
            'origin' => $request->input('origin'),
            'destination' => $request->input('destination'),
            'departure_date' => $request->input('departure_date'),
            'per_page' => $request->integer('per_page', 15),
        ], fn ($v) => $v !== null && $v !== '');

        $trips = $this->tripService->searchTrips($filters);

        return TripResource::collection($trips);
    }

    /**
     * POST /trips — Create a draft trip.
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        $this->authorize('create', Trip::class);

        try {
            $trip = $this->tripService->createDraft(
                $request->user(),
                $request->validated()
            );

            $trip->load(['host', 'vehicle.category', 'stops']);

            return response()->json(new TripResource($trip), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /trips/{trip} — Trip details with stops/host/vehicle.
     */
    public function show(Trip $trip): TripResource
    {
        $this->authorize('view', $trip);

        $trip->load(['host', 'vehicle.category', 'stops', 'bookings']);

        return new TripResource($trip);
    }

    /**
     * PATCH /trips/{trip} — Update draft trip only.
     */
    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        $this->authorize('update', $trip);

        $data = $request->validated();

        // Validate vehicle ownership if vehicle_id is being changed
        if (isset($data['vehicle_id'])) {
            $vehicle = Vehicle::where('id', $data['vehicle_id'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $vehicle) {
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
                    'message' => 'Cannot reduce total_seats below the number of already confirmed seats ('.$bookedSeats.').',
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
        $this->authorize('publish', $trip);

        try {
            $trip = $this->tripService->publishTrip($trip, $request->user());

            $trip->load(['host', 'vehicle.category', 'stops']);

            return response()->json([
                'message' => 'Trip published successfully.',
                'data' => new TripResource($trip),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /trips/{trip}/cancel — Cancel trip + pending bookings.
     */
    public function cancel(Request $request, Trip $trip): JsonResponse
    {
        $this->authorize('cancel', $trip);

        try {
            $trip = $this->tripService->cancelTrip($trip, $request->user());

            $trip->load(['host', 'vehicle.category', 'stops']);

            return response()->json([
                'message' => 'Trip cancelled successfully.',
                'data' => new TripResource($trip),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /trips/{trip}/start — Published/full → In progress.
     */
    public function start(Request $request, Trip $trip): JsonResponse
    {
        $this->authorize('start', $trip);

        try {
            $trip = $this->tripService->startTrip($trip, $request->user());

            $trip->load(['host', 'vehicle.category', 'stops']);

            return response()->json([
                'message' => 'Trip started successfully.',
                'data' => new TripResource($trip),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /trips/{trip}/complete — In progress → Completed.
     */
    public function complete(Request $request, Trip $trip): JsonResponse
    {
        $this->authorize('complete', $trip);

        try {
            $trip = $this->tripService->completeTrip($trip, $request->user());

            $trip->load(['host', 'vehicle.category', 'stops']);

            return response()->json([
                'message' => 'Trip completed successfully.',
                'data' => new TripResource($trip),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /trips/{trip}/booking-requests — Trip's bookings.
     */
    public function bookingRequests(Request $request, Trip $trip): AnonymousResourceCollection
    {
        $this->authorize('view', $trip);

        $bookings = $trip->bookings()
            ->with(['traveler', 'pickupStop', 'dropStop'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }
}
