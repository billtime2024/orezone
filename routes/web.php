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
    Route::get('/login', fn () => redirect('/coming-soon'));
    Route::get('/register', fn () => redirect('/coming-soon'));
    Route::get('/search', fn () => redirect('/coming-soon'));
});
