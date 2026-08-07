<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Send OTP to the given phone number.
     *
     * Creates or finds a user by phone and generates a 6-digit OTP
     * stored in cache for 5 minutes.
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

        Cache::put("otp:{$phone}", $otp, now()->addMinutes(5));

        $response = [
            'message' => 'OTP sent successfully',
            'phone'   => $phone,
        ];

        // Only return OTP in local environment (for development)
        if (app()->environment('local', 'testing')) {
            $response['otp_for_dev'] = $otp;
        }

        return response()->json($response);
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

        $cachedOtp = Cache::get("otp:{$phone}");

        if ($cachedOtp === null || (string) $cachedOtp !== (string) $otp) {
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

            // Delete the OTP from cache
            Cache::forget("otp:{$phone}");

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
