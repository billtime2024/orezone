<?php

namespace App\Services\Rental;

use App\Enums\RentalBookingStatus;
use App\Exceptions\BookingException;
use App\Models\RentalBooking;
use App\Models\RentalBookingStatusHistory;
use App\Models\RentalListing;
use App\Models\User;
use App\Notifications\RentalBookingAutoTransition;
use App\Notifications\RentalBookingCancelled;
use App\Notifications\RentalBookingConfirmed;
use App\Notifications\RentalBookingCreated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new booking for a listing.
     */
    public function createBooking(RentalListing $listing, array $data): RentalBooking
    {
        // Calculate nights/hours
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        if ($listing->price_unit === 'hour') {
            $nights = (int) $checkIn->diffInHours($checkOut);
        } else {
            $nights = (int) $checkIn->diffInDays($checkOut);
        }

        if ($nights < 1) {
            throw new BookingException('Minimum stay is 1 ' . $listing->price_unit . '.');
        }

        return DB::transaction(function () use ($listing, $data, $nights) {
            // Lock the listing row to prevent race conditions
            $lockedListing = RentalListing::lockForUpdate()->findOrFail($listing->id);

            // Validate availability inside the transaction with the lock held
            if (!$lockedListing->isAvailable($data['check_in'], $data['check_out'])) {
                throw new BookingException('Listing is not available for selected dates.');
            }

            $booking = RentalBooking::create([
                'rental_listing_id' => $lockedListing->id,
                'user_id' => Auth::id(),
                'owner_id' => $lockedListing->user_id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'nights' => $nights,
                'price_per_unit' => $lockedListing->price_per_unit,
                'cleaning_fee' => $lockedListing->cleaning_fee,
                'security_deposit' => $lockedListing->security_deposit,
                'guests_count' => $data['guests_count'] ?? 1,
                'guest_message' => $data['guest_message'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'booking_type' => $lockedListing->instant_booking ? 'instant' : 'request',
                'status' => $lockedListing->instant_booking ? 'confirmed' : 'pending',
            ]);

            $booking->calculateTotal();
            $booking->save();

            // Record status history
            $this->recordStatusHistory($booking, null, $booking->status, 'guest');

            // Update listing stats
            $lockedListing->increment('total_bookings');

            // Notify owner
            $owner = User::find($lockedListing->user_id);
            if ($owner) {
                $owner->notify(new RentalBookingCreated($booking));
            }

            return $booking;
        });
    }

    /**
     * Transition a booking to a new status via state machine.
     */
    public function transition(
        RentalBooking $booking,
        RentalBookingStatus $toStatus,
        string $actor,
        ?string $note = null,
    ): RentalBooking {
        return DB::transaction(function () use ($booking, $toStatus, $actor, $note) {
            $fresh = RentalBooking::lockForUpdate()->findOrFail($booking->id);
            $fromStatus = $fresh->getStatusEnum();

            if (!$fromStatus->canTransitionTo($toStatus)) {
                throw new BookingException(
                    "Invalid transition: {$fromStatus->value} → {$toStatus->value}"
                );
            }

            if (!$fromStatus->canActorTransitionTo($toStatus, $actor)) {
                throw new BookingException(
                    "Actor '{$actor}' not authorized for this transition."
                );
            }

            $updateData = ['status' => $toStatus->value];

            // Handle cancellation metadata
            if ($toStatus === RentalBookingStatus::CancelledByGuest) {
                $updateData['cancelled_by'] = 'guest';
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $note;
            } elseif ($toStatus === RentalBookingStatus::CancelledByHost) {
                $updateData['cancelled_by'] = 'host';
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $note;
            }

            $fresh->update($updateData);

            $this->recordStatusHistory($fresh, $fromStatus->value, $toStatus->value, $actor, $note);

            // Decrement total_bookings on cancellation, rejection, or expiry
            if (in_array($toStatus, [
                RentalBookingStatus::CancelledByGuest,
                RentalBookingStatus::CancelledByHost,
                RentalBookingStatus::Rejected,
                RentalBookingStatus::Expired,
            ])) {
                RentalListing::where('id', $fresh->rental_listing_id)
                    ->where('total_bookings', '>', 0)
                    ->decrement('total_bookings');
            }

            // Dispatch notifications
            $this->dispatchNotifications($fresh, $fromStatus->value, $toStatus->value, $actor);

            return $fresh;
        });
    }

    /**
     * Host confirms a booking.
     */
    public function confirmBooking(RentalBooking $booking, ?string $hostMessage = null): RentalBooking
    {
        if ($hostMessage) {
            $booking->update(['host_message' => $hostMessage]);
        }
        return $this->transition($booking, RentalBookingStatus::Confirmed, 'host');
    }

    /**
     * Host rejects a booking.
     */
    public function rejectBooking(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::Rejected, 'host', $reason);
    }

    /**
     * Guest cancels a booking.
     */
    public function cancelByGuest(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::CancelledByGuest, 'guest', $reason);
    }

    /**
     * Host cancels a booking.
     */
    public function cancelByHost(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::CancelledByHost, 'host', $reason);
    }

    /**
     * Auto check-in: Confirmed bookings where check_in = today → active.
     */
    public function autoCheckIn(): int
    {
        $bookings = RentalBooking::where('status', 'confirmed')
            ->whereDate('check_in', today())
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                $this->transition($booking, RentalBookingStatus::Active, 'system');
                $count++;
            } catch (BookingException $e) {
                \Log::warning("Auto check-in failed for booking {$booking->id}: {$e->getMessage()}");
            }
        }
        return $count;
    }

    /**
     * Auto check-out: Active bookings where check_out = today → completed.
     */
    public function autoCheckOut(): int
    {
        $bookings = RentalBooking::where('status', 'active')
            ->whereDate('check_out', today())
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                $this->transition($booking, RentalBookingStatus::Completed, 'system');
                $count++;
            } catch (BookingException $e) {
                \Log::warning("Auto check-out failed for booking {$booking->id}: {$e->getMessage()}");
            }
        }
        return $count;
    }

    /**
     * Auto expire: Pending bookings older than 48 hours → expired.
     */
    public function autoExpire(): int
    {
        $bookings = RentalBooking::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                $this->transition($booking, RentalBookingStatus::Expired, 'system');
                $count++;
            } catch (BookingException $e) {
                \Log::warning("Auto expire failed for booking {$booking->id}: {$e->getMessage()}");
            }
        }
        return $count;
    }

    /**
     * Record status change in history.
     */
    private function recordStatusHistory(
        RentalBooking $booking,
        ?string $from,
        string $to,
        string $actor,
        ?string $note = null,
    ): void {
        RentalBookingStatusHistory::create([
            'rental_booking_id' => $booking->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => Auth::id(),
            'actor_type' => $actor,
            'note' => $note,
        ]);
    }

    /**
     * Dispatch notifications based on status transition.
     */
    private function dispatchNotifications(
        RentalBooking $booking,
        string $fromStatus,
        string $toStatus,
        string $actor,
    ): void {
        // Reload relations
        $booking->load(['listing', 'guest', 'owner']);

        match ($toStatus) {
            'confirmed' => $this->notifyConfirmed($booking),
            'rejected', 'cancelled_by_guest', 'cancelled_by_host' => $this->notifyCancelled($booking, $toStatus),
            'active', 'completed', 'expired' => $this->notifyAutoTransition($booking, $fromStatus, $toStatus),
            default => null,
        };
    }

    private function notifyConfirmed(RentalBooking $booking): void
    {
        if ($booking->guest) {
            $booking->guest->notify(new RentalBookingConfirmed($booking));
        }
    }

    private function notifyCancelled(RentalBooking $booking, string $status): void
    {
        $cancelledBy = match ($status) {
            'cancelled_by_guest' => 'guest',
            'cancelled_by_host' => 'host',
            default => 'system',
        };

        // Notify the other party
        if ($cancelledBy === 'guest' && $booking->owner) {
            $booking->owner->notify(new RentalBookingCancelled($booking, $cancelledBy));
        } elseif ($cancelledBy === 'host' && $booking->guest) {
            $booking->guest->notify(new RentalBookingCancelled($booking, $cancelledBy));
        }
    }

    private function notifyAutoTransition(RentalBooking $booking, string $from, string $to): void
    {
        // Notify both guest and owner
        if ($booking->guest) {
            $booking->guest->notify(new RentalBookingAutoTransition($booking, $from, $to));
        }
        if ($booking->owner) {
            $booking->owner->notify(new RentalBookingAutoTransition($booking, $from, $to));
        }
    }
}
