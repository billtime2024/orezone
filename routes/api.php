<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TripController;
use App\Http\Controllers\Api\V1\VerificationController;
use App\Http\Controllers\Api\V1\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API versioned under /api/v1/ prefix.
| Public: send-otp, verify-otp
| Protected (auth:sanctum): logout, me
|
*/

Route::prefix('v1')->group(function () {

    // ── Public Routes ──────────────────────────────────────────────
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);

    // ── Protected Routes ───────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // ── Profile Routes ─────────────────────────────────────────
        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);

        // ── Verification Routes ────────────────────────────────────
        Route::get('verification/status', [VerificationController::class, 'status']);
        Route::post('verification/documents', [VerificationController::class, 'uploadDocument']);
        Route::delete('verification/documents/{document}', [VerificationController::class, 'destroyDocument']);

        // ── Vehicle Routes ─────────────────────────────────────────
        Route::apiResource('vehicles', VehicleController::class);
        Route::post('vehicles/{vehicle}/submit-verification', [VehicleController::class, 'submitVerification']);

        // ── Trip Routes ────────────────────────────────────────────
        Route::get('trips/my', [TripController::class, 'index']);
        Route::get('trips/search', [TripController::class, 'search']);
        Route::post('trips', [TripController::class, 'store']);
        Route::get('trips/{trip}', [TripController::class, 'show']);
        Route::patch('trips/{trip}', [TripController::class, 'update']);
        Route::post('trips/{trip}/publish', [TripController::class, 'publish']);
        Route::post('trips/{trip}/cancel', [TripController::class, 'cancel']);
        Route::post('trips/{trip}/start', [TripController::class, 'start']);
        Route::post('trips/{trip}/complete', [TripController::class, 'complete']);
        Route::get('trips/{trip}/booking-requests', [TripController::class, 'bookingRequests']);

        // ── Booking Routes ─────────────────────────────────────────
        Route::post('trips/{trip}/bookings', [BookingController::class, 'store']);
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{booking}', [BookingController::class, 'show']);
        Route::post('bookings/{booking}/accept', [BookingController::class, 'accept']);
        Route::post('bookings/{booking}/reject', [BookingController::class, 'reject']);
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::post('bookings/{booking}/complete', [BookingController::class, 'complete']);
    });
});
