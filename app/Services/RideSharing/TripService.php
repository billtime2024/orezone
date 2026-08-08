<?php

namespace App\Services\RideSharing;

use App\Events\TripCancelled;
use App\Events\TripPublished;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Trip;
use App\Models\TripStatusHistory;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TripService
{
    /**
     * Create a new trip in draft status.
     *
     * Validates that the host owns the vehicle and creates the trip
     * with available_seats equal to total_seats.
     */
    public function createDraft(User $host, array $data): Trip
    {
        // Validate vehicle ownership
        $vehicle = Vehicle::where('id', $data['vehicle_id'])
            ->where('user_id', $host->id)
            ->first();

        if (!$vehicle) {
            throw new InvalidArgumentException('Vehicle not found or does not belong to you.');
        }

        $trip = Trip::create([
            'host_id' => $host->id,
            'vehicle_id' => $vehicle->id,
            'origin_name' => $data['origin_name'] ?? null,
            'origin_address' => $data['origin_address'] ?? null,
            'origin_lat' => $data['origin_lat'] ?? null,
            'origin_lng' => $data['origin_lng'] ?? null,
            'destination_name' => $data['destination_name'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'destination_lat' => $data['destination_lat'] ?? null,
            'destination_lng' => $data['destination_lng'] ?? null,
            'departure_at' => $data['departure_at'] ?? null,
            'arrival_at' => $data['arrival_at'] ?? null,
            'total_seats' => $data['total_seats'],
            'available_seats' => $data['total_seats'],
            'booking_mode' => $data['booking_mode'] ?? Trip::BOOKING_MODE_REQUEST,
            'status' => Trip::STATUS_DRAFT,
            'route_polyline' => $data['route_polyline'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $trip;
    }

    /**
     * Publish a draft trip, making it available for bookings.
     *
     * Validates that the trip is in draft status, has at least 1 seat,
     * and the vehicle is verified.
     */
    public function publishTrip(Trip $trip, User $host): Trip
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        $vehicle = Vehicle::where('id', $trip->vehicle_id)->first();

        if (!$vehicle) {
            throw new InvalidArgumentException('Vehicle not found for this trip.');
        }

        return DB::transaction(function () use ($trip, $host, $vehicle) {
            $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

            if ($lockedTrip->status !== Trip::STATUS_DRAFT) {
                throw new InvalidArgumentException(
                    "Trip must be in draft status to publish. Current status: {$lockedTrip->status}"
                );
            }

            if ($lockedTrip->total_seats < 1) {
                throw new InvalidArgumentException('Trip must have at least 1 seat to publish.');
            }

            if ($vehicle->verification_status !== 'verified') {
                throw new InvalidArgumentException('Vehicle must be verified before publishing the trip.');
            }

            $lockedTrip->update(['status' => Trip::STATUS_PUBLISHED]);

            event(new TripPublished($lockedTrip));

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_PUBLISHED,
                $host->id
            );

            return $lockedTrip;
        });
    }

    /**
     * Cancel a trip, cancelling all active bookings on it.
     *
     * Only draft or published trips can be cancelled.
     */
    public function cancelTrip(Trip $trip, User $host): Trip
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        return DB::transaction(function () use ($trip, $host) {
            $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

            if (!$lockedTrip->canBeCancelled()) {
                throw new InvalidArgumentException(
                    "Trip cannot be cancelled in current status: {$lockedTrip->status}"
                );
            }

            $lockedTrip->update(['status' => Trip::STATUS_CANCELLED]);

            event(new TripCancelled($lockedTrip));

            // Cancel all active bookings (requested, accepted, confirmed)
            Booking::where('trip_id', $lockedTrip->id)
                ->whereIn('status', [
                    Booking::STATUS_REQUESTED,
                    Booking::STATUS_ACCEPTED,
                    Booking::STATUS_CONFIRMED,
                ])
                ->each(function (Booking $booking) use ($host, $lockedTrip) {
                    // Restore seats for bookings that had them decremented (accepted/confirmed)
                    if (in_array($booking->status, [Booking::STATUS_ACCEPTED, Booking::STATUS_CONFIRMED])) {
                        $lockedTrip->increment('available_seats', $booking->seat_count);
                    }

                    $booking->update([
                        'status' => Booking::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                    ]);

                    BookingStatusHistory::create([
                        'booking_id' => $booking->id,
                        'status' => Booking::STATUS_CANCELLED,
                        'changed_by' => $host->id,
                        'metadata' => ['reason' => 'Trip cancelled by host'],
                    ]);
                });

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_CANCELLED,
                $host->id
            );

            return $lockedTrip;
        });
    }

    /**
     * Start an active trip (host begins the journey).
     *
     * Only published trips can be started.
     */
    public function startTrip(Trip $trip, User $host): Trip
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        return DB::transaction(function () use ($trip, $host) {
            $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

            if (!in_array($lockedTrip->status, [Trip::STATUS_PUBLISHED, Trip::STATUS_FULL])) {
                throw new InvalidArgumentException(
                    "Trip must be published or full to start. Current status: {$lockedTrip->status}"
                );
            }

            if ($lockedTrip->available_seats > 0) {
                throw new InvalidArgumentException('Trip must be fully booked before starting. Available seats: ' . $lockedTrip->available_seats);
            }

            $lockedTrip->update(['status' => Trip::STATUS_IN_PROGRESS]);

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_IN_PROGRESS,
                $host->id
            );

            return $lockedTrip;
        });
    }

    /**
     * Complete an in-progress trip.
     */
    public function completeTrip(Trip $trip, User $host): Trip
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        return DB::transaction(function () use ($trip, $host) {
            $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

            if ($lockedTrip->status !== Trip::STATUS_IN_PROGRESS) {
                throw new InvalidArgumentException(
                    "Trip must be in progress to complete. Current status: {$lockedTrip->status}"
                );
            }

            $lockedTrip->update(['status' => Trip::STATUS_COMPLETED]);

            // Complete all confirmed/active bookings on this trip
            $lockedTrip->bookings()
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_ACTIVE, Booking::STATUS_ACCEPTED])
                ->each(function (Booking $booking) {
                    $booking->update([
                        'status' => Booking::STATUS_COMPLETED,
                        'completed_at' => now(),
                    ]);
                    BookingStatusHistory::create([
                        'booking_id' => $booking->id,
                        'status' => Booking::STATUS_COMPLETED,
                        'changed_by' => $booking->host_id,
                        'metadata' => ['reason' => 'Trip completed'],
                    ]);
                });

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_COMPLETED,
                $host->id
            );

            return $lockedTrip;
        });
    }

    /**
     * Search published trips with available seats.
     *
     * Supports origin, destination, and departure_date filters.
     */
    public function searchTrips(array $filters): LengthAwarePaginator
    {
        $query = Trip::query()
            ->published()
            ->where('available_seats', '>', 0)
            ->with(['host:id,name,phone', 'vehicle:id,brand,model,year,color,seating_capacity']);

        // Origin filter (searches origin_name or origin_address)
        if (!empty($filters['origin'])) {
            $origin = addcslashes($filters['origin'], '%_');
            $query->where(function ($q) use ($origin) {
                $q->where('origin_name', 'like', "%{$origin}%")
                  ->orWhere('origin_address', 'like', "%{$origin}%");
            });
        }

        // Destination filter (searches destination_name or destination_address)
        if (!empty($filters['destination'])) {
            $destination = addcslashes($filters['destination'], '%_');
            $query->where(function ($q) use ($destination) {
                $q->where('destination_name', 'like', "%{$destination}%")
                  ->orWhere('destination_address', 'like', "%{$destination}%");
            });
        }

        // Departure date filter
        if (!empty($filters['departure_date'])) {
            $query->whereDate('departure_at', $filters['departure_date']);
        }

        // Departure time range filter
        if (!empty($filters['departure_from'])) {
            $query->where('departure_at', '>=', $filters['departure_from']);
        }
        if (!empty($filters['departure_to'])) {
            $query->where('departure_at', '<=', $filters['departure_to']);
        }

        // Minimum seats available
        if (!empty($filters['min_seats'])) {
            $query->where('available_seats', '>=', $filters['min_seats']);
        }

        // Maximum seats (filter trips by total capacity)
        if (!empty($filters['max_seats'])) {
            $query->where('total_seats', '<=', $filters['max_seats']);
        }

        // Booking mode filter
        if (!empty($filters['booking_mode'])) {
            $query->where('booking_mode', $filters['booking_mode']);
        }

        // Radius-based search using lat/lng
        if (!empty($filters['origin_lat']) && !empty($filters['origin_lng'])) {
            $radiusKm = $filters['radius_km'] ?? 50; // Default 50km
            $radiusMeters = $radiusKm * 1000;

            // Haversine formula for radius search
            $query->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(origin_lat))
                    * cos(radians(origin_lng) - radians(?))
                    + sin(radians(?)) * sin(radians(origin_lat))
                )) <= ?
            ", [
                $filters['origin_lat'],
                $filters['origin_lng'],
                $filters['origin_lat'],
                $radiusMeters,
            ]);
        }

        // Destination radius search
        if (!empty($filters['dest_lat']) && !empty($filters['dest_lng'])) {
            $radiusKm = $filters['dest_radius_km'] ?? 50;
            $radiusMeters = $radiusKm * 1000;

            $query->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(destination_lat))
                    * cos(radians(destination_lng) - radians(?))
                    + sin(radians(?)) * sin(radians(destination_lat))
                )) <= ?
            ", [
                $filters['dest_lat'],
                $filters['dest_lng'],
                $filters['dest_lat'],
                $radiusMeters,
            ]);
        }

        // Vehicle category filter
        if (!empty($filters['vehicle_category'])) {
            $query->whereHas('vehicle', function ($q) use ($filters) {
                $q->where('vehicle_category_id', $filters['vehicle_category']);
            });
        }

        // Sort
        $sortBy = $filters['sort'] ?? 'departure_at';
        $sortDir = $filters['direction'] ?? 'asc';
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'asc';
        $allowedSorts = ['departure_at', 'created_at', 'total_seats', 'available_seats'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Update a draft trip.
     *
     * Validates ownership, draft status, and recalculates available_seats
     * when total_seats changes.
     */
    public function updateDraft(Trip $trip, User $host, array $data): Trip
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        if ($trip->status !== Trip::STATUS_DRAFT) {
            throw new InvalidArgumentException('Only draft trips can be updated.');
        }

        // Validate vehicle ownership if vehicle_id is being changed
        if (isset($data['vehicle_id'])) {
            $vehicle = Vehicle::where('id', $data['vehicle_id'])
                ->where('user_id', $host->id)
                ->first();

            if (!$vehicle) {
                throw new InvalidArgumentException('Vehicle not found or does not belong to you.');
            }
        }

        // Recalculate available_seats when total_seats changes
        if (isset($data['total_seats']) && $data['total_seats'] !== $trip->total_seats) {
            $newTotal = $data['total_seats'];
            $bookedSeats = $trip->bookings()
                ->where('status', Booking::STATUS_CONFIRMED)
                ->sum('seat_count');

            if ($bookedSeats > $newTotal) {
                throw new InvalidArgumentException(
                    "Cannot reduce total_seats below the number of already confirmed seats ({$bookedSeats})."
                );
            }

            $data['available_seats'] = $newTotal - $bookedSeats;
        }

        $trip->update($data);

        return $trip;
    }

    /**
     * Delete a draft trip.
     *
     * Only draft trips can be deleted.
     */
    public function deleteDraft(Trip $trip, User $host): void
    {
        if (!$trip->isHost($host)) {
            throw new InvalidArgumentException('You are not the host of this trip.');
        }

        if ($trip->status !== Trip::STATUS_DRAFT) {
            throw new InvalidArgumentException('Only draft trips can be deleted.');
        }

        $trip->delete();
    }

    /**
     * Check if a trip should be marked as 'full' when available_seats reaches 0.
     *
     * Called after a booking is confirmed and seats are decremented.
     * Only applies to published trips.
     */
    public function checkAndMarkFull(Trip $trip): Trip
    {
        $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

        if ($lockedTrip->status === Trip::STATUS_PUBLISHED && $lockedTrip->available_seats <= 0) {
            $lockedTrip->update(['status' => Trip::STATUS_FULL]);

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_FULL,
                $lockedTrip->host_id
            );
        }

        return $lockedTrip;
    }

    /**
     * Check if a trip should be restored from 'full' to 'published' when seats increase.
     *
     * Called after a booking is cancelled and seats are restored.
     * Only applies to trips that were marked as 'full'.
     */
    public function checkAndRestoreFromFull(Trip $trip): Trip
    {
        $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

        if ($lockedTrip->status === Trip::STATUS_FULL && $lockedTrip->available_seats > 0) {
            $lockedTrip->update(['status' => Trip::STATUS_PUBLISHED]);

            $this->recordTripStatusHistory(
                $lockedTrip,
                Trip::STATUS_PUBLISHED,
                $lockedTrip->host_id
            );
        }

        return $lockedTrip;
    }

    /**
     * Record a status change in the trip's history.
     */
    private function recordTripStatusHistory(Trip $trip, string $status, int $changedBy): void
    {
        $trip->statusHistory()->create([
            'status' => $status,
            'changed_by' => $changedBy,
        ]);
    }
}
