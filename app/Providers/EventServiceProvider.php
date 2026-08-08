<?php

namespace App\Providers;

use App\Events\BookingAccepted;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\TripCancelled;
use App\Events\TripPublished;
use App\Events\WalletDebited;
use App\Listeners\RecordWalletTransaction;
use App\Listeners\SendBookingNotification;
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
