<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Report;
use App\Models\Review;
use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VerificationRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrezoneController extends Controller
{
    public function landing()
    {
        return Inertia::render('landing/index');
    }

    public function coming_soon()
    {
        return Inertia::render('pages/coming-soon');
    }

    /**
     * Admin: Dashboard with stats
     */
    public function admin_dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_trips' => Trip::count(),
            'published_trips' => Trip::where('status', 'published')->count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_vehicles' => Vehicle::count(),
            'pending_verifications' => VerificationRequest::where('status', 'pending')->count(),
            'total_wallets' => Wallet::count(),
            'total_balance' => (float) Wallet::sum('balance'),
            'total_reviews' => Review::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'active_sos' => SosAlert::where('status', 'active')->count(),
        ];

        return Inertia::render('admin/dashboard', ['stats' => $stats]);
    }

    /**
     * Admin: Users list
     */
    public function admin_users(Request $request)
    {
        $query = User::with('profile')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/users/index', ['users' => $users]);
    }

    /**
     * Admin: Verification requests
     */
    public function admin_verifications(Request $request)
    {
        $query = VerificationRequest::with(['user', 'documents'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/verifications/index', ['verificationRequests' => $requests]);
    }

    /**
     * Admin: Vehicles list
     */
    public function admin_vehicles(Request $request)
    {
        $query = Vehicle::with(['user', 'category'])->latest();

        if ($request->filled('category')) {
            $query->where('vehicle_category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/vehicles/index', ['vehicles' => $vehicles]);
    }

    /**
     * Admin: Trips list
     */
    public function admin_trips(Request $request)
    {
        $query = Trip::with(['host', 'vehicle'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $trips = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/trips/index', ['trips' => $trips]);
    }

    /**
     * Admin: Bookings list
     */
    public function admin_bookings(Request $request)
    {
        $query = Booking::with(['trip', 'traveler', 'trip.host'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/bookings/index', ['bookings' => $bookings]);
    }

    /**
     * Admin: Wallets list
     */
    public function admin_wallets(Request $request)
    {
        $query = Wallet::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $wallets = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/wallets/index', ['wallets' => $wallets]);
    }

    /**
     * Admin: Reviews list
     */
    public function admin_reviews(Request $request)
    {
        $query = Review::with(['reviewer', 'reviewee', 'trip'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/reviews/index', ['reviews' => $reviews]);
    }

    /**
     * Admin: Reports list
     */
    public function admin_reports(Request $request)
    {
        $query = Report::with(['reporter', 'reportedUser'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/reports/index', ['reports' => $reports]);
    }

    /**
     * Admin: SOS alerts list
     */
    public function admin_sos(Request $request)
    {
        $query = SosAlert::with(['user', 'trip'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $alerts = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/sos/index', ['alerts' => $alerts]);
    }
}
