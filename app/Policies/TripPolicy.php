<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any trips.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific trip.
     */
    public function view(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            || $trip->status === 'published';
    }

    /**
     * Determine whether the user can create a trip.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the trip.
     */
    public function update(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && $trip->status === 'draft';
    }

    /**
     * Determine whether the user can delete the trip.
     */
    public function delete(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && $trip->status === 'draft';
    }

    /**
     * Determine whether the user can publish the trip.
     */
    public function publish(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && $trip->status === 'draft';
    }

    /**
     * Determine whether the user can cancel the trip.
     */
    public function cancel(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && in_array($trip->status, ['draft', 'published']);
    }

    /**
     * Determine whether the user can start the trip.
     */
    public function start(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && $trip->status === 'published';
    }

    /**
     * Determine whether the user can complete the trip.
     */
    public function complete(User $user, Trip $trip): bool
    {
        return $user->id === $trip->host_id
            && $trip->status === 'in_progress';
    }
}
