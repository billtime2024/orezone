<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PortalController extends Controller
{
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

        $validated['user_id'] = $request->user()->id;
        $validated['verification_status'] = 'pending';
        $validated['is_active'] = true;

        Vehicle::create($validated);

        return redirect()->route('portal.vehicles')->with('success', 'Vehicle added successfully.');
    }

    /**
     * Show vehicle detail.
     */
    public function showVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
        }

        $vehicle->load(['category', 'documents']);

        $vehicleData = [
            'id' => $vehicle->id,
            'vehicle_category_id' => $vehicle->vehicle_category_id,
            'registration_number' => $vehicle->registration_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'color' => $vehicle->color,
            'seating_capacity' => $vehicle->seating_capacity,
            'verification_status' => $vehicle->verification_status,
            'is_active' => $vehicle->is_active,
            'category' => $vehicle->category ? [
                'id' => $vehicle->category->id,
                'name' => $vehicle->category->name,
            ] : null,
            'documents' => $vehicle->documents->map(fn ($d) => [
                'id' => $d->id,
                'type' => $d->type ?? 'Document',
                'status' => $d->status ?? 'uploaded',
            ]),
            'created_at' => $vehicle->created_at,
            'updated_at' => $vehicle->updated_at,
        ];

        return Inertia::render('portal/vehicles/show', [
            'user' => $request->user(),
            'vehicle' => $vehicleData,
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

        $vehicle->load('category');

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
     * Delete a vehicle.
     */
    public function destroyVehicle(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== $request->user()->id) {
            abort(403, 'This vehicle does not belong to you.');
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

        $vehicle->update(['verification_status' => 'pending']);

        return redirect()->route('portal.vehicles.show', $vehicle)->with('success', 'Vehicle submitted for verification.');
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
                'status' => $trip->status,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
                'booking_mode' => $trip->booking_mode,
                'vehicle' => $trip->vehicle ? [
                    'id' => $trip->vehicle->id,
                    'brand' => $trip->vehicle->brand,
                    'model' => $trip->vehicle->model,
                ] : null,
            ]);

        return Inertia::render('portal/trips/index', [
            'user' => $request->user(),
            'trips' => $trips,
        ]);
    }

    /**
     * Search published trips.
     */
    public function searchTrips(Request $request)
    {
        $query = Trip::published()
            ->with(['host', 'vehicle.category', 'stops'])
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
            ->paginate($request->integer('per_page', 15))
            ->through(fn ($trip) => [
                'id' => $trip->id,
                'origin_name' => $trip->origin_name,
                'destination_name' => $trip->destination_name,
                'departure_at' => $trip->departure_at,
                'status' => $trip->status,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
                'booking_mode' => $trip->booking_mode,
                'host' => $trip->host ? [
                    'id' => $trip->host->id,
                    'name' => $trip->host->name,
                ] : null,
                'vehicle' => $trip->vehicle ? [
                    'brand' => $trip->vehicle->brand,
                    'model' => $trip->vehicle->model,
                ] : null,
            ]);

        return Inertia::render('portal/trips/search', [
            'user' => $request->user(),
            'trips' => $trips,
            'filters' => $request->only(['origin', 'destination', 'departure_date']),
        ]);
    }

    /**
     * Show create trip form.
     */
    public function createTrip(Request $request)
    {
        $vehicles = $request->user()
            ->vehicles()
            ->where('verification_status', 'approved')
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
     * Store a new trip (as draft).
     */
    public function storeTrip(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'departure_at' => 'required|date|after:now',
            'total_seats' => 'required|integer|min:1|max:20',
            'booking_mode' => 'required|in:instant,request',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify vehicle belongs to user
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $vehicle) {
            return back()->withErrors(['vehicle_id' => 'Vehicle does not belong to you.']);
        }

        $trip = Trip::create([
            'host_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'origin_name' => $validated['origin_name'],
            'destination_name' => $validated['destination_name'],
            'departure_at' => $validated['departure_at'],
            'total_seats' => $validated['total_seats'],
            'available_seats' => $validated['total_seats'],
            'booking_mode' => $validated['booking_mode'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
        ]);

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip created successfully as draft.');
    }

    /**
     * Show trip detail.
     */
    public function showTrip(Request $request, Trip $trip)
    {
        $trip->load(['host', 'vehicle.category', 'stops', 'bookings.traveler', 'bookings.pickupStop', 'bookings.dropStop']);

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
                'email' => $trip->host->email,
            ] : null,
            'vehicle' => $trip->vehicle ? [
                'id' => $trip->vehicle->id,
                'brand' => $trip->vehicle->brand,
                'model' => $trip->vehicle->model,
                'registration_number' => $trip->vehicle->registration_number,
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
            'bookings' => $trip->bookings->map(fn ($b) => [
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
            ]),
        ];

        return Inertia::render('portal/trips/show', [
            'user' => $request->user(),
            'trip' => $tripData,
            'isHost' => $trip->host_id === $request->user()->id,
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
            ->where('verification_status', 'approved')
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
     * Update a trip (draft only).
     */
    public function updateTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if ($trip->status !== 'draft') {
            return back()->withErrors(['status' => 'Only draft trips can be updated.']);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'departure_at' => 'required|date|after:now',
            'total_seats' => 'required|integer|min:1|max:20',
            'booking_mode' => 'required|in:instant,request',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify vehicle belongs to user
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $vehicle) {
            return back()->withErrors(['vehicle_id' => 'Vehicle does not belong to you.']);
        }

        // Recalculate available seats
        $bookedSeats = $trip->bookings()->where('status', 'confirmed')->sum('seat_count');
        $validated['available_seats'] = $validated['total_seats'] - $bookedSeats;
        $validated['vehicle_id'] = $vehicle->id;

        $trip->update($validated);

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip updated successfully.');
    }

    /**
     * Delete a trip (draft only).
     */
    public function destroyTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if ($trip->status !== 'draft') {
            return back()->withErrors(['status' => 'Only draft trips can be deleted.']);
        }

        $trip->delete();

        return redirect()->route('portal.trips')->with('success', 'Trip deleted successfully.');
    }

    /**
     * Publish a draft trip.
     */
    public function publishTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (! $trip->canBePublished()) {
            return back()->withErrors(['status' => 'Only draft trips can be published.']);
        }

        $trip->update(['status' => 'published']);

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip published successfully.');
    }

    /**
     * Cancel a trip.
     */
    public function cancelTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (! $trip->canBeCancelled()) {
            return back()->withErrors(['status' => 'This trip cannot be cancelled in its current status.']);
        }

        DB::transaction(function () use ($trip) {
            $trip->update(['status' => 'cancelled']);

            // Cancel pending/requested bookings
            $trip->bookings()->whereIn('status', ['requested', 'confirmed'])->each(function ($booking) use ($trip) {
                if ($booking->status === 'confirmed') {
                    $trip->increment('available_seats', $booking->seat_count);
                }
                $booking->update(['status' => 'cancelled']);
            });
        });

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip cancelled successfully.');
    }

    /**
     * Start a trip (published + fully booked → in_progress).
     */
    public function startTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (! $trip->canBeStarted()) {
            return back()->withErrors(['status' => 'Trip can only be started when published and fully booked.']);
        }

        $trip->update(['status' => 'in_progress']);

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip started successfully.');
    }

    /**
     * Complete a trip.
     */
    public function completeTrip(Request $request, Trip $trip)
    {
        if ($trip->host_id !== $request->user()->id) {
            abort(403, 'This trip does not belong to you.');
        }

        if (! $trip->canBeCompleted()) {
            return back()->withErrors(['status' => 'Only in-progress trips can be completed.']);
        }

        DB::transaction(function () use ($trip) {
            $trip->update([
                'status' => 'completed',
                'arrival_at' => now(),
            ]);
            $trip->confirmedBookings()->update(['status' => 'completed']);
        });

        return redirect()->route('portal.trips.show', $trip)->with('success', 'Trip completed successfully.');
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
     * Store a booking for a trip.
     */
    public function storeBooking(Request $request, Trip $trip)
    {
        $user = $request->user();

        // Trip must be published
        if ($trip->status !== 'published') {
            return back()->withErrors(['trip' => 'This trip is not available for booking.']);
        }

        // Cannot book own trip
        if ($trip->host_id === $user->id) {
            return back()->withErrors(['trip' => 'You cannot book your own trip.']);
        }

        $validated = $request->validate([
            'seat_count' => 'required|integer|min:1',
            'pickup_stop_id' => 'nullable|exists:trip_stops,id',
            'drop_stop_id' => 'nullable|exists:trip_stops,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $seatCount = $validated['seat_count'];

        // Validate stops belong to this trip
        if (! empty($validated['pickup_stop_id']) || ! empty($validated['drop_stop_id'])) {
            $validStopIds = $trip->stops()->pluck('id')->toArray();
            if (! empty($validated['pickup_stop_id']) && ! in_array($validated['pickup_stop_id'], $validStopIds)) {
                return back()->withErrors(['pickup_stop_id' => 'Selected pickup stop does not belong to this trip.']);
            }
            if (! empty($validated['drop_stop_id']) && ! in_array($validated['drop_stop_id'], $validStopIds)) {
                return back()->withErrors(['drop_stop_id' => 'Selected drop-off stop does not belong to this trip.']);
            }
        }

        $booking = DB::transaction(function () use ($trip, $user, $validated, $seatCount) {
            $trip = Trip::lockForUpdate()->find($trip->id);

            if ($trip->available_seats < $seatCount) {
                throw new \App\Exceptions\InsufficientSeatsException(
                    'Not enough seats available. Available: ' . $trip->available_seats
                );
            }

            $bookingMode = $trip->booking_mode;

            // Only decrement seats immediately for instant bookings
            if ($bookingMode === 'instant') {
                $trip->decrement('available_seats', $seatCount);
            }

            $status = $bookingMode === 'instant' ? 'confirmed' : 'requested';

            return Booking::create([
                'trip_id' => $trip->id,
                'traveler_id' => $user->id,
                'host_id' => $trip->host_id,
                'pickup_stop_id' => $validated['pickup_stop_id'] ?? null,
                'drop_stop_id' => $validated['drop_stop_id'] ?? null,
                'seat_count' => $seatCount,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking created successfully.');
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
     * Cancel a booking.
     */
    public function cancelBooking(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($booking->traveler_id !== $user->id && $booking->trip->host_id !== $user->id) {
            abort(403, 'You do not have permission to cancel this booking.');
        }

        if (! $booking->canBeCancelled()) {
            return back()->withErrors(['status' => 'This booking cannot be cancelled in its current status.']);
        }

        $wasConfirmed = $booking->status === 'confirmed';

        DB::transaction(function () use ($booking, $wasConfirmed) {
            $booking->update(['status' => 'cancelled']);
            if ($wasConfirmed) {
                Trip::where('id', $booking->trip_id)
                    ->increment('available_seats', $booking->seat_count);
            }
        });

        return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Complete a booking (host only, trip must be in_progress).
     */
    public function completeBooking(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($booking->trip->host_id !== $user->id) {
            abort(403, 'You are not the host of this trip.');
        }

        if (! $booking->canBeCompleted()) {
            return back()->withErrors(['status' => 'This booking cannot be completed in its current status.']);
        }

        $booking->update(['status' => 'completed']);

        return redirect()->route('portal.bookings.show', $booking)->with('success', 'Booking marked as completed.');
    }

    // ── Wallet & Profile ──────────────────────────────────────────

    /**
     * Show the user's wallet and transaction history.
     */
    public function wallet(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;
        $transactions = collect();

        if ($wallet) {
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
        }

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
