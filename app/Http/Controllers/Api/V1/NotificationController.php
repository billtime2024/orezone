<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * GET /notifications — List user's notifications.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->integer('per_page', 20));

        return NotificationResource::collection($notifications);
    }

    /**
     * PATCH /notifications/{notification}/read — Mark a notification as read.
     */
    public function markAsRead(Request $request, AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403, 'This notification does not belong to you.');
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * PATCH /notifications/read-all — Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = AppNotification::markAllAsRead($request->user()->id);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'count' => $count,
        ]);
    }
}
