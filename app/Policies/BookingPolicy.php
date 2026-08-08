<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->traveler_id
            || $user->id === $booking->host_id;
    }

    /**
     * Determine whether the user can create a booking on a trip.
     */
    public function create(User $user, Trip $trip): bool
    {
        return $user->id !== $trip->host_id
            && $trip->status === 'published';
    }

    /**
     * Determine whether the user can accept the booking.
     */
    public function accept(User $user, Booking $booking): bool
    {
        return $user->id === $booking->host_id
            && $booking->status === 'requested';
    }

    /**
     * Determine whether the user can reject the booking.
     */
    public function reject(User $user, Booking $booking): bool
    {
        return $user->id === $booking->host_id
            && $booking->status === 'requested';
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        $isParticipant = $user->id === $booking->traveler_id
            || $user->id === $booking->host_id;

        return $isParticipant
            && in_array($booking->status, ['requested', 'accepted', 'confirmed']);
    }

    /**
     * Determine whether the user can complete the booking.
     */
    public function complete(User $user, Booking $booking): bool
    {
        return $user->id === $booking->host_id
            && in_array($booking->status, ['accepted', 'confirmed']);
    }
}
