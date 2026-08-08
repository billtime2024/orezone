<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Models\User;
class RentalBookingPolicy
{
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
    public function view(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->owner_id
            || $user->is_admin;
    }

    /**
     * Determine whether the user can create a booking on a listing.
     */
    public function create(User $user, RentalListing $listing): bool
    {
        return $user->id !== $listing->user_id
            && $listing->status === 'active';
    }

    /**
     * Determine whether the user can confirm the booking (host only).
     */
    public function confirm(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->owner_id
            && $booking->status === 'pending';
    }

    /**
     * Determine whether the user can reject the booking (host only).
     */
    public function reject(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->owner_id
            && $booking->status === 'pending';
    }

    /**
     * Determine whether the user can cancel as guest.
     */
    public function cancelByGuest(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id
            && in_array($booking->status, ['pending', 'confirmed']);
    }

    /**
     * Determine whether the user can cancel as host.
     */
    public function cancelByHost(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->owner_id
            && in_array($booking->status, ['pending', 'confirmed']);
    }
}
