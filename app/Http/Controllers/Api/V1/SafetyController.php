<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportRequest;
use App\Http\Requests\Api\V1\StoreSosRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Http\Resources\Api\V1\SosAlertResource;
use App\Models\BlockedUser;
use App\Models\Booking;
use App\Models\Report;
use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafetyController extends Controller
{
    /**
     * POST /reports — Report a user.
     */
    public function storeReport(StoreReportRequest $request): JsonResponse
    {
        $user = $request->user();
        $reportedUserId = $request->validated('reported_user_id');
        $bookingId = $request->validated('booking_id');
        $tripId = $request->validated('trip_id');

        // Validate the booking connects the reporter to the reported user
        $booking = Booking::where('id', $bookingId)
            ->where(function ($query) use ($user, $reportedUserId) {
                $query->where('traveler_id', $user->id)
                    ->where('host_id', $reportedUserId);
            })
            ->orWhere(function ($query) use ($user, $reportedUserId) {
                $query->where('host_id', $user->id)
                    ->where('traveler_id', $reportedUserId);
            })
            ->first();

        if (! $booking) {
            return response()->json([
                'message' => 'The specified booking does not connect you with the reported user.',
            ], 422);
        }

        // If a trip_id was supplied, it must belong to that booking
        if ($tripId && (int) $booking->trip_id !== (int) $tripId) {
            return response()->json([
                'message' => 'The trip does not belong to the specified booking.',
            ], 422);
        }

        $report = Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUserId,
            'trip_id' => $tripId ?: $booking->trip_id,
            'booking_id' => $booking->id,
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
        ]);

        $report->load(['reporter', 'reportedUser', 'trip', 'booking']);

        return response()->json([
            'message' => 'Report submitted successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    /**
     * POST /users/{user}/block — Block a user.
     */
    public function blockUser(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json([
                'message' => 'You cannot block yourself.',
            ], 422);
        }

        BlockedUser::firstOrCreate([
            'user_id' => $currentUser->id,
            'blocked_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'User blocked successfully.',
        ]);
    }

    /**
     * POST /sos — Trigger SOS alert.
     */
    public function triggerSos(StoreSosRequest $request): JsonResponse
    {
        $user = $request->user();

        $sos = SosAlert::create([
            'user_id' => $user->id,
            'trip_id' => $request->validated('trip_id'),
            'latitude' => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'message' => $request->validated('message'),
            'status' => SosAlert::STATUS_ACTIVE,
        ]);

        $sos->load(['user', 'trip']);

        // In production: push notifications to emergency contacts and admin dashboard

        return response()->json([
            'message' => 'SOS alert recorded. Emergency contacts and the support team have been notified.',
            'data' => new SosAlertResource($sos),
        ], 201);
    }
}
