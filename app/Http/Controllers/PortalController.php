<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\RideSharing\TripService;
use App\Services\RideSharing\BookingService;
use App\Services\RideSharing\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class PortalController extends Controller
{
    public function __construct(
        private readonly TripService $tripService,
        private readonly BookingService $bookingService,
        private readonly WalletService $walletService,
    ) {}

    /**
     * Portal dashboard.
     */
    public function index(Request $request)
    {
        return Inertia::render('portal/index', [
            'user' => $request->user(),
        ]);
    }

    // ── Vehicle Routes ────────────────────────────────────────────

    /**
     * List user's vehicles.
     */
    public function vehicles(Request $request)
    {
        $vehicles = $request->user()
            ->vehicles()
            ->with('category')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'vehicle_category_id' => $v->vehicle_category_id,
                'registration_number' => $v->registration_number,
                'brand' => $v->brand,
                'model' => $v->model,
                'year' => $v->year,
                'color' => $v->color,
                'seating_capacity' => $v->seating_capacity,
                'verification_status' => $v->verification_status,
                'is_active' => $v->is_active,
                'category' => $v->category ? [
                    'id' => $v->category->id,
                    'name' => $v->category->name,
                ] : null,
                'created_at' => $v->created_at,
                'updated_at' => $v->updated_at,
            ]);

        $categories = VehicleCategory::where('is_active', true)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]);

        return Inertia::render('portal/vehicles/index', [
            'user' => $request->user(),
            'vehicles' => $vehicles,
            'categories' => $categories,
        ]);
    }

    /**
     * Show create vehicle form.
     */
    public function createVehicle(Request $request)
    {
        $categories = VehicleCategory::where('is_active', true)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]);

        return Inertia::render('portal/vehicles/create', [
            'user' => $request->user(),
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new vehicle.
     */
    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'registration_number' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:255',
            'seating_capacity' => 'required|integer|min:1|max:20',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $request->user()->id,
            'vehicle_category_id' => $validated['vehicle_category_id'],
            'registration_number' => $validated['registration_number'],
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'color' => $validated['color'] ?? null,
            'seating_capacity' => $validated['seating_capacity'],
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        return redirect()->route('portal.vehicles.show', $vehicle)->with('success', 'Vehicle added successfully.');
    }

    /**
     * Show vehicle detail.
     */
    public function showVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $vehicle->load('category');

        return Inertia::render('portal/vehicles/show', [
            'user' => $request->user(),
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Show edit vehicle form.
     */
    public function editVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $categories = VehicleCategory::where('is_active', true)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]);

        return Inertia::render('portal/vehicles/edit', [
            'user' => $request->user(),
            'vehicle' => $vehicle,
            'categories' => $categories,
        ]);
    }

    /**
     * Update a vehicle.
     */
    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $validated = $request->validate([
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'registration_number' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:255',
            'seating_capacity' => 'required|integer|min:1|max:20',
        ]);

        $vehicle->update($validated);

        return redirect()->route('portal.vehicles.show', $vehicle)->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Delete a vehicle (only if no active trips).
     */
    public function destroyVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $hasActiveTrips = $vehicle->trips()
            ->whereIn('status', ['draft', 'published', 'in_progress'])
            ->exists();

        if ($hasActiveTrips) {
            return back()->withErrors(['vehicle' => 'Cannot delete vehicle with active trips.']);
        }

        $vehicle->delete();

        return redirect()->route('portal.vehicles')->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Submit vehicle for verification.
     */
    public function submitVehicleVerification(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $vehicle->update(['verification_status' => 'pending_review']);

        return back()->with('success', 'Vehicle submitted for verification.');
    }

    // ── Trip Routes ───────────────────────────────────────────────

    /**
     * List user's trips (as host).
     */
    public function trips(Request $request)
    {
        $trips = $request->user()
            ->trips()
            ->with(['vehicle.category', 'stops'])
            ->orderByDesc('departure_at')
            ->get()
            ->map(fn ($trip) => [
                'id' => $trip->id,
                'origin_name' => $trip->origin_name,
                'destination_name' => $trip->destination_name,
                'departure_at' => $trip->departure_at,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
                'booking_mode' => $trip->booking_mode,
                'status' => $trip->status,
                'vehicle' => $trip->vehicle ? [
                    'brand' => $trip->vehicle->brand,
                    'model' => $trip->vehicle->model,
                    'category' => $trip->vehicle->category ? [
                        'name' => $trip->vehicle->category->name,
                    ] : null,
                ] : null,
                'created_at' => $trip->created_at,
            ]);

        return Inertia::render('portal/trips/index', [
            'user' => $request->user(),
            'trips' => $trips,
        ]);
    }

    /**
     * Search published trips (for booking).
     */
    public function searchTrips(Request $request)
    {
        $filters = array_filter([
            'origin' => $request->input('origin'),
            'destination' => $request->input('destination'),
            'departure_date' => $request->input('departure_date'),
            'min_seats' => $request->input('min_seats'),
            'booking_mode' => $request->input('booking_mode'),
            'per_page' => $request->integer('per_page', 15),
        ], fn ($v) => $v !== null && $v !== '');

        $trips = $this->tripService->searchTrips($filters);

        return Inertia::render('portal/trips/search', [
            'user' => $request->user(),
            'trips' => $trips,
            'filters' => $request->only(['origin', 'destination', 'departure_date', 'min_seats', 'booking_mode']),
        ]);
    }

    /**
     * Show create trip form.
     */
    public function createTrip(Request $request)
    {
        $vehicles = $request->user()
            ->vehicles()
            ->where('verification_status', 'verified')
            ->where('is_active', true)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'brand' => $v->brand,
                'model' => $v->model,
                'registration_number' => $v->registration_number,
                'seating_capacity' => $v->seating_capacity,
            ]);

        return Inertia::render('portal/trips/create', [
            'user' => $request->user(),
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Store a new trip (uses TripService).
     */
    public function storeTrip(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'departure_at' => 'required|date|after:now',
            'total_seats' => 'required|integer|min:1|max:20',
            'booking_mode' => 'required|in:instant,request',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $trip = $this->tripService->createDraft($request->user(), $validated);

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip created successfully as draft.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['vehicle_id' => $e->getMessage()]);
        }
    }

    /**
     * Show trip detail.
     */
    public function showTrip(Request $request, Trip $trip)
    {
        $trip->load(['host', 'vehicle.category', 'stops', 'bookings.traveler', 'bookings.pickupStop', 'bookings.dropStop']);

        $isHost = $trip->host_id === $request->user()->id;

        // Draft trips can only be viewed by the host
        if ($trip->status === 'draft' && ! $isHost) {
            abort(403, 'This trip is not published yet.');
        }

        $tripData = [
            'id' => $trip->id,
            'host_id' => $trip->host_id,
            'origin_name' => $trip->origin_name,
            'origin_address' => $trip->origin_address,
            'destination_name' => $trip->destination_name,
            'destination_address' => $trip->destination_address,
            'departure_at' => $trip->departure_at,
            'arrival_at' => $trip->arrival_at,
            'total_seats' => $trip->total_seats,
            'available_seats' => $trip->available_seats,
            'booking_mode' => $trip->booking_mode,
            'status' => $trip->status,
            'notes' => $trip->notes,
            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
            'host' => $trip->host ? [
                'id' => $trip->host->id,
                'name' => $trip->host->name,
                'email' => $isHost ? $trip->host->email : null,
            ] : null,
            'vehicle' => $trip->vehicle ? [
                'id' => $trip->vehicle->id,
                'brand' => $trip->vehicle->brand,
                'model' => $trip->vehicle->model,
                'registration_number' => $isHost ? $trip->vehicle->registration_number : null,
                'category' => $trip->vehicle->category ? [
                    'name' => $trip->vehicle->category->name,
                ] : null,
            ] : null,
            'stops' => $trip->stops->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'stop_order' => $s->stop_order,
                'estimated_arrival' => $s->estimated_arrival,
            ]),
            'bookings' => $isHost
                ? $trip->bookings->map(fn ($b) => [
                    'id' => $b->id,
                    'seat_count' => $b->seat_count,
                    'status' => $b->status,
                    'created_at' => $b->created_at,
                    'traveler' => $b->traveler ? [
                        'id' => $b->traveler->id,
                        'name' => $b->traveler->name,
                        'email' => $b->traveler->email,
                    ] : null,
                    'pickup_stop' => $b->pickupStop ? [
                        'id' => $b->pickupStop->id,
                        'name' => $b->pickupStop->name,
                    ] : null,
                    'drop_stop' => $b->dropStop ? [
                        'id' => $b->dropStop->id,
                        'name' => $b->dropStop->name,
                    ] : null,
                ])
                : $trip->bookings->map(fn ($b) => [
                    'id' => $b->id,
                    'seat_count' => $b->seat_count,
                    'status' => $b->status,
                    'created_at' => $b->created_at,
                ]),
        ];

        return Inertia::render('portal/trips/show', [
            'user' => $request->user(),
            'trip' => $tripData,
            'isHost' => $isHost,
        ]);
    }

    /**
     * Show edit trip form (draft only).
     */
    public function editTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        $vehicles = $request->user()
            ->vehicles()
            ->where('verification_status', 'verified')
            ->where('is_active', true)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'brand' => $v->brand,
                'model' => $v->model,
                'registration_number' => $v->registration_number,
                'seating_capacity' => $v->seating_capacity,
            ]);

        return Inertia::render('portal/trips/edit', [
            'user' => $request->user(),
            'trip' => $trip,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Update a trip (uses TripService).
     */
    public function updateTrip(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'departure_at' => 'required|date|after:now',
            'total_seats' => 'required|integer|min:1|max:20',
            'booking_mode' => 'required|in:instant,request',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->tripService->updateDraft($trip, $request->user(), $validated);

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Delete a trip (uses TripService).
     */
    public function destroyTrip(Request $request, Trip $trip)
    {
        try {
            $this->tripService->deleteDraft($trip, $request->user());

            return redirect()->route('portal.trips')->with('success', 'Trip deleted successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Publish a draft trip (uses TripService).
     */
    public function publishTrip(Request $request, Trip $trip)
    {
        try {
            $this->tripService->publishTrip($trip, $request->user());

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip published successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a trip (uses TripService).
     */
    public function cancelTrip(Request $request, Trip $trip)
    {
        try {
            $this->tripService->cancelTrip($trip, $request->user());

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip cancelled successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Start a trip (uses TripService).
     */
    public function startTrip(Request $request, Trip $trip)
    {
        try {
            $this->tripService->startTrip($trip, $request->user());

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip started successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Complete a trip (uses TripService).
     */
    public function completeTrip(Request $request, Trip $trip)
    {
        try {
            $this->tripService->completeTrip($trip, $request->user());

            return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip completed successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Show book trip form.
     */
    public function bookTrip(Request $request, Trip $trip)
    {
        $trip->load(['host', 'vehicle.category', 'stops']);

        $tripData = [
            'id' => $trip->id,
            'origin_name' => $trip->origin_name,
            'destination_name' => $trip->destination_name,
            'departure_at' => $trip->departure_at,
            'total_seats' => $trip->total_seats,
            'available_seats' => $trip->available_seats,
            'booking_mode' => $trip->booking_mode,
            'status' => $trip->status,
            'notes' => $trip->notes,
            'host' => $trip->host ? [
                'id' => $trip->host->id,
                'name' => $trip->host->name,
            ] : null,
            'stops' => $trip->stops->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'stop_order' => $s->stop_order,
            ]),
        ];

        return Inertia::render('portal/trips/book', [
            'user' => $request->user(),
            'trip' => $tripData,
        ]);
    }

    /**
     * Store a booking for a trip (uses BookingService).
     */
    public function storeBooking(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'seat_count' => 'required|integer|min:1|max:20',
            'pickup_stop_id' => 'nullable|exists:trip_stops,id',
            'drop_stop_id' => 'nullable|exists:trip_stops,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = $this->bookingService->createBooking($trip, $request->user(), $validated);

            return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking created successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['trip' => $e->getMessage()]);
        }
    }

    // ── Booking Routes ────────────────────────────────────────────

    /**
     * List user's bookings (as traveler).
     */
    public function bookings(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('trip:id,origin_name,destination_name,departure_at,status,host_id')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'trip' => [
                    'id' => $booking->trip?->id,
                    'origin_name' => $booking->trip?->origin_name,
                    'destination_name' => $booking->trip?->destination_name,
                    'departure_at' => $booking->trip?->departure_at,
                    'status' => $booking->trip?->status,
                    'is_host' => $booking->trip?->host_id === $request->user()->id,
                ],
                'seat_count' => $booking->seat_count,
                'status' => $booking->status,
                'created_at' => $booking->created_at,
            ]);

        return Inertia::render('portal/bookings/index', [
            'user' => $request->user(),
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show booking detail.
     */
    public function showBooking(Request $request, Booking $booking)
    {
        $booking->load('trip');

        if ($booking->traveler_id !== $request->user()->id
            && $booking->trip->host_id !== $request->user()->id) {
            abort(403, 'You do not have access to this booking.');
        }

        $booking->load(['trip.host', 'trip.vehicle.category', 'trip.stops', 'traveler', 'pickupStop', 'dropStop']);

        $bookingData = [
            'id' => $booking->id,
            'seat_count' => $booking->seat_count,
            'status' => $booking->status,
            'platform_fee' => $booking->platform_fee,
            'total_platform_fee' => $booking->total_platform_fee,
            'requested_at' => $booking->requested_at,
            'accepted_at' => $booking->accepted_at,
            'confirmed_at' => $booking->confirmed_at,
            'cancelled_at' => $booking->cancelled_at,
            'completed_at' => $booking->completed_at,
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
            'trip' => $booking->trip ? [
                'id' => $booking->trip->id,
                'origin_name' => $booking->trip->origin_name,
                'destination_name' => $booking->trip->destination_name,
                'departure_at' => $booking->trip->departure_at,
                'status' => $booking->trip->status,
                'booking_mode' => $booking->trip->booking_mode,
                'host_id' => $booking->trip->host_id,
                'host' => $booking->trip->host ? [
                    'id' => $booking->trip->host->id,
                    'name' => $booking->trip->host->name,
                    'email' => $booking->trip->host->email,
                ] : null,
                'vehicle' => $booking->trip->vehicle ? [
                    'brand' => $booking->trip->vehicle->brand,
                    'model' => $booking->trip->vehicle->model,
                    'category' => $booking->trip->vehicle->category ? [
                        'name' => $booking->trip->vehicle->category->name,
                    ] : null,
                ] : null,
                'stops' => $booking->trip->stops->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                ]),
            ] : null,
            'traveler' => $booking->traveler ? [
                'id' => $booking->traveler->id,
                'name' => $booking->traveler->name,
                'email' => $booking->traveler->email,
            ] : null,
            'host' => $booking->trip->host ? [
                'id' => $booking->trip->host->id,
                'name' => $booking->trip->host->name,
                'email' => $booking->trip->host->email,
            ] : null,
            'pickup_stop' => $booking->pickupStop ? [
                'id' => $booking->pickupStop->id,
                'name' => $booking->pickupStop->name,
            ] : null,
            'drop_stop' => $booking->dropStop ? [
                'id' => $booking->dropStop->id,
                'name' => $booking->dropStop->name,
            ] : null,
        ];

        return Inertia::render('portal/bookings/show', [
            'user' => $request->user(),
            'booking' => $bookingData,
            'isHost' => $booking->trip->host_id === $request->user()->id,
        ]);
    }

    /**
     * Cancel a booking (uses BookingService).
     */
    public function cancelBooking(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->cancelBooking($booking, $request->user());

            return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking cancelled successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Complete a booking (uses BookingService).
     */
    public function completeBooking(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->completeBooking($booking, $request->user());

            return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking marked as completed.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    // ── Wallet & Profile ──────────────────────────────────────────

    /**
     * Show the user's wallet and transaction history (uses WalletService).
     */
    public function wallet(Request $request)
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $transactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'direction' => $tx->direction,
                'amount' => $tx->amount,
                'created_at' => $tx->created_at,
            ]);

        return Inertia::render('portal/wallet', [
            'user' => $user,
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the user's profile edit form.
     */
    public function profile(Request $request)
    {
        return Inertia::render('portal/profile', [
            'user' => $request->user(),
            'success' => session('success'),
        ]);
    }
}
