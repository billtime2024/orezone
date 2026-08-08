<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RentalListing;
use App\Models\User;
class RentalListingPolicy
{
    /**
     * Determine whether the user can view any listings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific listing.
     */
    public function view(User $user, RentalListing $listing): bool
    {
        return $listing->status === 'active'
            || $user->id === $listing->user_id
            || $user->is_admin;
    }

    /**
     * Determine whether the user can create a listing.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the listing.
     */
    public function update(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    /**
     * Determine whether the user can delete the listing.
     */
    public function delete(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    /**
     * Determine whether the user can manage dates (block/unblock).
     */
    public function manageDates(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    /**
     * Determine whether the user can toggle listing status.
     */
    public function toggleStatus(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    /**
     * Determine whether the user can upload photos.
     */
    public function uploadPhotos(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    /**
     * Determine whether the user can delete photos.
     */
    public function deletePhoto(User $user, RentalListing $listing): bool
    {
        return $user->id === $listing->user_id;
    }
}
