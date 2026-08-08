<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any vehicles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific vehicle.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id;
    }

    /**
     * Determine whether the user can create a vehicle.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the vehicle.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id;
    }

    /**
     * Determine whether the user can delete the vehicle.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id;
    }

    /**
     * Determine whether the user can submit the vehicle for verification.
     */
    public function submitVerification(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id;
    }
}
