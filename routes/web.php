<?php

use App\Http\Controllers\OrezoneController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalRentalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Phase 1: Public landing page and coming-soon placeholders.
| Auth and protected routes will be added in later phases.
|
*/

Route::controller(OrezoneController::class)->group(function () {
    // Public landing page
    Route::get('/', 'landing');

    // Coming-soon placeholder for future pages
    Route::get('/coming-soon', 'coming_soon');

    // Redirect placeholder routes to coming-soon
    Route::redirect('/register', '/coming-soon');
    Route::redirect('/search', '/coming-soon');
});

// Admin routes — authenticated users with admin flag only
Route::middleware(['auth', 'can:access-admin'])->controller(OrezoneController::class)->group(function () {
    Route::get('/admin', 'admin_dashboard')->name('admin.dashboard');
    Route::get('/admin/users', 'admin_users')->name('admin.users');
    Route::get('/admin/verifications', 'admin_verifications')->name('admin.verifications');
    Route::get('/admin/vehicles', 'admin_vehicles')->name('admin.vehicles');
    Route::get('/admin/trips', 'admin_trips')->name('admin.trips');
    Route::get('/admin/bookings', 'admin_bookings')->name('admin.bookings');
    Route::get('/admin/wallets', 'admin_wallets')->name('admin.wallets');
    Route::get('/admin/reviews', 'admin_reviews')->name('admin.reviews');
    Route::get('/admin/reports', 'admin_reports')->name('admin.reports');
    Route::get('/admin/sos', 'admin_sos')->name('admin.sos');
    Route::get('/admin/profile', 'admin_profile')->name('admin.profile');
    Route::get('/admin/change-password', 'admin_change_password')->name('admin.change-password');
});

// Admin Rental Module Routes
Route::middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/admin/rentals', [App\Http\Controllers\AdminRentalController::class, 'index'])->name('admin.rentals');
    Route::get('/admin/rentals/{listing}', [App\Http\Controllers\AdminRentalController::class, 'show'])->name('admin.rentals.show');
    Route::get('/admin/rentals-bookings', [App\Http\Controllers\AdminRentalBookingController::class, 'index'])->name('admin.rentals-bookings');
    Route::get('/admin/rentals-bookings/{booking}', [App\Http\Controllers\AdminRentalBookingController::class, 'show'])->name('admin.rentals-bookings.show');
});

// Portal routes — authenticated users only
Route::middleware(['auth'])->controller(PortalController::class)->group(function () {
    // Dashboard
    Route::get('/portal', 'index')->name('portal.index');

    // Vehicle routes
    Route::get('/portal/vehicles', 'vehicles')->name('portal.vehicles');
    Route::get('/portal/vehicles/create', 'createVehicle')->name('portal.vehicles.create');
    Route::post('/portal/vehicles', 'storeVehicle')->name('portal.vehicles.store');
    Route::get('/portal/vehicles/{vehicle}', 'showVehicle')->name('portal.vehicles.show');
    Route::get('/portal/vehicles/{vehicle}/edit', 'editVehicle')->name('portal.vehicles.edit');
    Route::put('/portal/vehicles/{vehicle}', 'updateVehicle')->name('portal.vehicles.update');
    Route::delete('/portal/vehicles/{vehicle}', 'destroyVehicle')->name('portal.vehicles.destroy');
    Route::post('/portal/vehicles/{vehicle}/verify', 'submitVehicleVerification')->name('portal.vehicles.verify');

    // Trip routes
    Route::get('/portal/trips', 'trips')->name('portal.trips');
    Route::get('/portal/trips/search', 'searchTrips')->name('portal.trips.search');
    Route::get('/portal/trips/create', 'createTrip')->name('portal.trips.create');
    Route::post('/portal/trips', 'storeTrip')->name('portal.trips.store');
    Route::get('/portal/trips/{trip}', 'showTrip')->name('portal.trips.show');
    Route::get('/portal/trips/{trip}/edit', 'editTrip')->name('portal.trips.edit');
    Route::put('/portal/trips/{trip}', 'updateTrip')->name('portal.trips.update');
    Route::delete('/portal/trips/{trip}', 'destroyTrip')->name('portal.trips.destroy');
    Route::post('/portal/trips/{trip}/publish', 'publishTrip')->name('portal.trips.publish');
    Route::post('/portal/trips/{trip}/cancel', 'cancelTrip')->name('portal.trips.cancel');
    Route::post('/portal/trips/{trip}/start', 'startTrip')->name('portal.trips.start');
    Route::post('/portal/trips/{trip}/complete', 'completeTrip')->name('portal.trips.complete');
    Route::get('/portal/trips/{trip}/book', 'bookTrip')->name('portal.trips.book');
    Route::post('/portal/trips/{trip}/bookings', 'storeBooking')->name('portal.trips.bookings.store');

    // Booking routes
    Route::get('/portal/bookings', 'bookings')->name('portal.bookings');
    Route::get('/portal/bookings/{booking}', 'showBooking')->name('portal.bookings.show');
    Route::post('/portal/bookings/{booking}/cancel', 'cancelBooking')->name('portal.bookings.cancel');
    Route::post('/portal/bookings/{booking}/complete', 'completeBooking')->name('portal.bookings.complete');

    // Wallet & Profile
    Route::get('/portal/wallet', 'wallet')->name('portal.wallet');
    Route::get('/portal/profile', 'profile')->name('portal.profile');
});

// Portal Rental Module Routes
Route::middleware(['auth'])->controller(PortalRentalController::class)->group(function () {
    // Browse
    Route::get('/portal/rentals', 'index')->name('portal.rentals');
    Route::get('/portal/rentals/my', 'myListings')->name('portal.rentals.my');
    Route::get('/portal/rentals/create', 'create')->name('portal.rentals.create');
    Route::post('/portal/rentals', 'store')->name('portal.rentals.store');
    Route::get('/portal/rentals/{listing}/edit', 'edit')->name('portal.rentals.edit');
    Route::put('/portal/rentals/{listing}', 'update')->name('portal.rentals.update');
    Route::delete('/portal/rentals/{listing}', 'destroy')->name('portal.rentals.destroy');
    Route::post('/portal/rentals/{listing}/toggle-status', 'toggleStatus')->name('portal.rentals.toggle');
    Route::get('/portal/rentals/{listing}', 'show')->name('portal.rentals.show');
    Route::get('/portal/rentals/{listing}/calendar', 'calendar')->name('portal.rentals.calendar');

    // Bookings
    Route::post('/portal/rentals/{listing}/bookings', 'storeBooking')->name('portal.rentals.bookings.store')->middleware('throttle:10,1');
    Route::get('/portal/rentals-bookings', 'myBookings')->name('portal.rentals-bookings');
    Route::get('/portal/owner/rentals-bookings', 'ownerBookings')->name('portal.owner.rentals-bookings');
    Route::post('/portal/rentals-bookings/{booking}/confirm', 'confirmBooking')->name('portal.rentals-bookings.confirm');
    Route::post('/portal/rentals-bookings/{booking}/reject', 'rejectBooking')->name('portal.rentals-bookings.reject');
    Route::post('/portal/rentals-bookings/{booking}/cancel', 'cancelBooking')->name('portal.rentals-bookings.cancel');
    Route::post('/portal/rentals-bookings/{booking}/host-cancel', 'hostCancelBooking')->name('portal.rentals-bookings.host-cancel');
});
