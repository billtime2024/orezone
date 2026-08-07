<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportRequest;
use App\Http\Requests\Api\V1\StoreSosRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Http\Resources\Api\V1\SosAlertResource;
use App\Models\BlockedUser;
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

        $report = Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $request->validated('reported_user_id'),
            'trip_id' => $request->validated('trip_id'),
            'booking_id' => $request->validated('booking_id'),
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
            'message' => 'SOS alert triggered. Emergency services have been notified.',
            'data' => new SosAlertResource($sos),
        ], 201);
    }
}
