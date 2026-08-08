<?php

use App\Http\Controllers\OrezoneController;
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
