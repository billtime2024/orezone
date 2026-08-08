<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\WalletTransaction;
use App\Observers\BookingObserver;
use App\Observers\TripObserver;
use App\Observers\WalletTransactionObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register observers for audit logging
        Trip::observe(TripObserver::class);
        Booking::observe(BookingObserver::class);
        WalletTransaction::observe(WalletTransactionObserver::class);
    }
}
