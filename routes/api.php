<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
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
    });
});
