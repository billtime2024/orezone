<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Vehicle;
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
}
