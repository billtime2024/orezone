<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Trip::class => \App\Policies\TripPolicy::class,
        \App\Models\Booking::class => \App\Policies\BookingPolicy::class,
        \App\Models\Vehicle::class => \App\Policies\VehiclePolicy::class,
        \App\Models\Wallet::class => \App\Policies\WalletPolicy::class,
        \App\Models\RentalListing::class => \App\Policies\RentalListingPolicy::class,
        \App\Models\RentalBooking::class => \App\Policies\RentalBookingPolicy::class,
        \App\Models\RentalReview::class => \App\Policies\RentalReviewPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function ($user) {
            return $user->is_admin === true;
        });
    }
}
