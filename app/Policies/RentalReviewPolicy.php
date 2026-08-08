<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RentalReview;
use App\Models\User;
class RentalReviewPolicy
{
    /**
     * Determine whether the user can view reviews.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific review.
     */
    public function view(User $user, RentalReview $review): bool
    {
        return $review->is_visible || $user->id === $review->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can create a review.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can toggle visibility (admin only).
     */
    public function toggleVisibility(User $user, RentalReview $review): bool
    {
        return $user->is_admin;
    }
}
