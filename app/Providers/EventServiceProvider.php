<?php

namespace App\Providers;

use App\Events\BookingAccepted;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\RentalBookingCancelled;
use App\Events\RentalBookingConfirmed;
use App\Events\RentalBookingCreated;
use App\Events\RentalBookingRejected;
use App\Events\TripCancelled;
use App\Events\TripPublished;
use App\Events\WalletDebited;
use App\Listeners\RecordWalletTransaction;
use App\Listeners\SendBookingNotification;
use App\Listeners\SendRentalBookingNotification;
use App\Listeners\SendTripNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Trip events
        TripPublished::class => [
            SendTripNotification::class,
        ],
        TripCancelled::class => [
            SendTripNotification::class,
        ],

        // Booking events
        BookingCreated::class => [
            SendBookingNotification::class,
        ],
        BookingAccepted::class => [
            SendBookingNotification::class,
        ],
        BookingConfirmed::class => [
            SendBookingNotification::class,
        ],
        BookingCancelled::class => [
            SendBookingNotification::class,
        ],

        // Wallet events
        WalletDebited::class => [
            RecordWalletTransaction::class,
        ],

        // Rental Booking events
        RentalBookingCreated::class => [
            SendRentalBookingNotification::class,
        ],
        RentalBookingConfirmed::class => [
            SendRentalBookingNotification::class,
        ],
        RentalBookingCancelled::class => [
            SendRentalBookingNotification::class,
        ],
        RentalBookingRejected::class => [
            SendRentalBookingNotification::class,
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
