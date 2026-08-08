<?php

namespace App\Services\RideSharing;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Trip;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\BookingAccepted;
use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private readonly CancellationPolicy $cancellationPolicy,
        private readonly TripService $tripService,
    ) {}

    /**
     * Create a new booking on a trip.
     *
     * Validates trip is published, traveler is not the host,
     * seats are available, and enforces idempotency.
     * Instant bookings are confirmed immediately; request bookings
     * start as "requested" awaiting host approval.
     */
    public function createBooking(Trip $trip, User $traveler, array $data): Booking
    {
        $seatCount = $data['seat_count'] ?? 1;
        $idempotencyKey = $data['idempotency_key'] ?? Str::uuid()->toString();

        // Basic pre-transaction validations (fast-fail before acquiring locks)
        // Allow 'full' trips for idempotency — duplicate request should return existing booking
        if (!in_array($trip->status, [Trip::STATUS_PUBLISHED, Trip::STATUS_FULL])) {
            throw new InvalidArgumentException('Trip is not available for booking.');
        }

        if ($trip->isHost($traveler)) {
            throw new InvalidArgumentException('You cannot book your own trip.');
        }

        if ($seatCount < 1) {
            throw new InvalidArgumentException('Must book at least 1 seat.');
        }

        return DB::transaction(function () use ($trip, $traveler, $data, $seatCount, $idempotencyKey) {
            // Idempotency check FIRST: return existing booking if duplicate request
            // This must come before seat/status checks so duplicate requests are idempotent
            $existing = Booking::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            // Lock the trip row to prevent race conditions on seat count
            $lockedTrip = Trip::lockForUpdate()->findOrFail($trip->id);

            if (!in_array($lockedTrip->status, [Trip::STATUS_PUBLISHED, Trip::STATUS_FULL])) {
                throw new InvalidArgumentException('Trip is no longer available for booking.');
            }

            if ($lockedTrip->available_seats < $seatCount) {
                throw new InvalidArgumentException(
                    "Not enough seats available. Requested: {$seatCount}, Available: {$lockedTrip->available_seats}"
                );
            }

            // Determine status based on booking mode
            $isInstant = $lockedTrip->booking_mode === Trip::BOOKING_MODE_INSTANT;
            $status = $isInstant ? Booking::STATUS_CONFIRMED : Booking::STATUS_REQUESTED;

            $now = now();

            $booking = Booking::create([
                'trip_id' => $lockedTrip->id,
                'traveler_id' => $traveler->id,
                'host_id' => $lockedTrip->host_id,
                'seat_count' => $seatCount,
                'pickup_stop_id' => $data['pickup_stop_id'] ?? null,
                'drop_stop_id' => $data['drop_stop_id'] ?? null,
                'status' => $status,
                'idempotency_key' => $idempotencyKey,
                'requested_at' => $now,
                'confirmed_at' => $isInstant ? $now : null,
            ]);

            // Decrement available seats for instant bookings
            if ($isInstant) {
                $lockedTrip->decrement('available_seats', $seatCount);

                // Auto-mark trip as full if no seats remain
                $this->tripService->checkAndMarkFull($lockedTrip);
            }

            // Record status history
            $this->recordBookingStatusHistory(
                $booking,
                $status,
                $traveler->id
            );

            event(new BookingCreated($booking));

            return $booking;
        });
    }

    /**
     * Host accepts a requested booking.
     *
     * Decrements available seats and transitions to accepted.
     */
    public function acceptBooking(Booking $booking, User $host): Booking
    {
        return DB::transaction(function () use ($booking, $host) {
            // Lock the trip row to prevent race conditions
            $lockedTrip = Trip::lockForUpdate()->findOrFail($booking->trip_id);

            if (!$lockedTrip->isHost($host)) {
                throw new InvalidArgumentException('You are not the host of this trip.');
            }

            $freshBooking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (!$freshBooking->canBeAccepted()) {
                throw new InvalidArgumentException(
                    "Booking cannot be accepted in current status: {$freshBooking->status}"
                );
            }

            if ($lockedTrip->available_seats < $freshBooking->seat_count) {
                throw new InvalidArgumentException(
                    "Not enough seats available. Requested: {$freshBooking->seat_count}, Available: {$lockedTrip->available_seats}"
                );
            }

            // Decrement available seats
            $lockedTrip->decrement('available_seats', $freshBooking->seat_count);

            // Auto-mark trip as full if no seats remain
            $this->tripService->checkAndMarkFull($lockedTrip);

            $freshBooking->update([
                'status' => Booking::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            $this->recordBookingStatusHistory(
                $freshBooking,
                Booking::STATUS_ACCEPTED,
                $host->id
            );

            event(new BookingAccepted($freshBooking));

            return $freshBooking;
        });
    }

    /**
     * Host rejects a requested booking.
     */
    public function rejectBooking(Booking $booking, User $host): Booking
    {
        return DB::transaction(function () use ($booking, $host) {
            if (!$booking->trip->isHost($host)) {
                throw new InvalidArgumentException('You are not the host of this trip.');
            }

            if (!$booking->canBeRejected()) {
                throw new InvalidArgumentException(
                    "Booking cannot be rejected in current status: {$booking->status}"
                );
            }

            $booking->update([
                'status' => Booking::STATUS_REJECTED,
            ]);

            $this->recordBookingStatusHistory(
                $booking,
                Booking::STATUS_REJECTED,
                $host->id
            );

            return $booking;
        });
    }

    /**
     * Cancel a booking (by traveler or host).
     *
     * Applies configurable cancellation rules:
     *   - Traveler before acceptance: full refund
     *   - Traveler after confirmation: partial refund (configurable)
     *   - Host cancellation: full refund + optional host penalty
     *   - No-show: platform retains fees
     *
     * Restores available seats if the booking was confirmed/accepted.
     */
    public function cancelBooking(Booking $booking, User $user): Booking
    {
        return DB::transaction(function () use ($booking, $user) {
            // Lock the trip row to safely restore seats
            $lockedTrip = Trip::lockForUpdate()->findOrFail($booking->trip_id);

            $freshBooking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (!$freshBooking->canBeCancelled()) {
                throw new InvalidArgumentException(
                    "Booking cannot be cancelled in current status: {$freshBooking->status}"
                );
            }

            // Verify the user is either the traveler or the host
            $isTraveler = $freshBooking->traveler_id === $user->id;
            $isHost = $lockedTrip->isHost($user);

            if (!$isTraveler && !$isHost) {
                throw new InvalidArgumentException('You are not authorized to cancel this booking.');
            }

            // Determine cancellation outcome using configurable policy
            $outcome = $this->cancellationPolicy->determineOutcome($freshBooking, $user);
            $refundAmount = $this->cancellationPolicy->calculateRefundAmount($freshBooking, $outcome);

            $wasSeatReserved = in_array($freshBooking->status, [
                Booking::STATUS_ACCEPTED,
                Booking::STATUS_CONFIRMED,
            ]);

            // Restore seats if they were previously decremented
            if ($wasSeatReserved) {
                $lockedTrip->increment('available_seats', $freshBooking->seat_count);

                // Restore trip from 'full' to 'published' if seats available again
                $this->tripService->checkAndRestoreFromFull($lockedTrip);
            }

            // Apply refund if applicable (credit back to traveler's wallet)
            if ($refundAmount > 0) {
                $travelerWallet = Wallet::lockForUpdate()
                    ->where('user_id', $freshBooking->traveler_id)
                    ->first();
                if ($travelerWallet) {
                    $balanceBefore = $travelerWallet->balance;
                    $travelerWallet->increment('balance', $refundAmount);

                    // Record the refund transaction
                    \App\Models\WalletTransaction::create([
                        'wallet_id' => $travelerWallet->id,
                        'user_id' => $freshBooking->traveler_id,
                        'direction' => 'credit',
                        'amount' => $refundAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceBefore + $refundAmount,
                        'type' => 'refund',
                        'status' => 'completed',
                        'reference_type' => Booking::class,
                        'reference_id' => $freshBooking->id,
                        'metadata' => [
                            'reason' => $outcome['description'],
                            'refund_percentage' => $outcome['refund_percentage'],
                        ],
                    ]);
                }
            }

            $freshBooking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'fee_snapshot' => [
                    'cancellation_outcome' => $outcome,
                    'refund_amount' => $refundAmount,
                ],
            ]);

            $this->recordBookingStatusHistory(
                $freshBooking,
                Booking::STATUS_CANCELLED,
                $user->id,
                ['cancellation_outcome' => $outcome]
            );

            event(new BookingCancelled($freshBooking, $user));

            return $freshBooking;
        });
    }

    /**
     * Host marks a booking as completed after the trip is in progress.
     *
     * Booking must be in accepted or confirmed status and the trip
     * must be in_progress.
     */
    public function completeBooking(Booking $booking, User $host): Booking
    {
        return DB::transaction(function () use ($booking, $host) {
            if (!$booking->trip->isHost($host)) {
                throw new InvalidArgumentException('You are not the host of this trip.');
            }

            if (!$booking->canBeCompleted()) {
                throw new InvalidArgumentException(
                    "Booking cannot be completed in current status: {$booking->status}. " .
                    "Booking must be accepted or confirmed and the trip must be in progress."
                );
            }

            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->recordBookingStatusHistory(
                $booking,
                Booking::STATUS_COMPLETED,
                $host->id
            );

            return $booking;
        });
    }

    /**
     * Record a status change in the booking's history.
     */
    private function recordBookingStatusHistory(Booking $booking, string $status, int $changedBy, array $metadata = []): void
    {
        $booking->statusHistory()->create([
            'status' => $status,
            'changed_by' => $changedBy,
            'metadata' => $metadata ?: null,
        ]);
    }
}
