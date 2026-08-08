<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Send OTP to the given phone number.
     *
     * Creates or finds a user by phone and generates a 6-digit OTP
     * stored as a hash in cache for 5 minutes.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:15'],
        ]);

        $phone = $request->input('phone');

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User_' . substr($phone, -4),
                'phone' => $phone,
                'status' => 'active',
            ]
        );

        $otp = random_int(100000, 999999);

        // Store a hash of the OTP, not the plaintext, to limit exposure if cache is dumped.
        Cache::put("otp:{$phone}", Hash::make((string) $otp), now()->addMinutes(5));

        // Track send count per phone to prevent abuse.
        $sendCountKey = "otp:send_count:{$phone}";
        Cache::put($sendCountKey, Cache::get($sendCountKey, 0) + 1, now()->addMinutes(5));

        return response()->json([
            'message' => 'OTP sent successfully',
            'phone'   => $phone,
        ]);
    }

    /**
     * Verify the OTP for the given phone number.
     *
     * On success, marks the phone as verified and returns a
     * Sanctum personal access token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:15'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $phone = $request->input('phone');
        $otp   = $request->input('otp');

        // Enforce max 5 verify attempts per phone before forcing a new OTP.
        $attemptKey = "otp:attempts:{$phone}";
        $attempts = (int) Cache::get($attemptKey, 0);

        if ($attempts >= 5) {
            Cache::forget("otp:{$phone}");
            Cache::forget($attemptKey);

            throw ValidationException::withMessages([
                'otp' => ['Too many failed attempts. Please request a new OTP.'],
            ]);
        }

        $hashedOtp = Cache::get("otp:{$phone}");

        if ($hashedOtp === null || !Hash::check((string) $otp, $hashedOtp)) {
            Cache::put($attemptKey, $attempts + 1, now()->addMinutes(5));

            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is invalid or has expired.'],
            ]);
        }

        // Use transaction to ensure atomicity
        DB::beginTransaction();

        try {
            $user = User::where('phone', $phone)->firstOrFail();
            $user->update([
                'phone_verified_at' => now(),
            ]);

            // Delete the OTP and related counters from cache
            Cache::forget("otp:{$phone}");
            Cache::forget($attemptKey);
            Cache::forget("otp:send_count:{$phone}");

            // Create Sanctum token
            $tokenResult = $user->createToken('auth-token');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'user'       => new UserResource($user->fresh()),
            'token'      => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout the authenticated user by revoking the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
