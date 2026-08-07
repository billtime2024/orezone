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
    Route::redirect('/login', '/coming-soon');
    Route::redirect('/register', '/coming-soon');
    Route::redirect('/search', '/coming-soon');

    // Admin routes
    Route::get('/admin/users', 'admin_users')->name('admin.users');
    Route::get('/admin/verifications', 'admin_verifications')->name('admin.verifications');
    Route::get('/admin/vehicles', 'admin_vehicles')->name('admin.vehicles');
});
