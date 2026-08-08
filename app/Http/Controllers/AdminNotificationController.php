<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminNotificationController extends Controller
{
    /**
     * List all notifications (admin view).
     */
    public function index(Request $request)
    {
        $query = Notification::with('user:id,name,email');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by read status
        if ($request->filled('read_status')) {
            if ($request->input('read_status') === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->input('read_status') === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return Inertia::render('admin/notifications/index', [
            'notifications' => $notifications,
            'filters' => $request->only(['user_id', 'type', 'read_status']),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
