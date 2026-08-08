<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('portal/index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show the user's trips (as host).
     */
    public function trips(Request $request)
    {
        $trips = $request->user()
            ->trips()
            ->with([])
            ->orderByDesc('departure_at')
            ->get()
            ->map(fn ($trip) => [
                'id' => $trip->id,
                'origin_name' => $trip->origin_name,
                'destination_name' => $trip->destination_name,
                'departure_at' => $trip->departure_at,
                'status' => $trip->status,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
            ]);

        return Inertia::render('portal/trips', [
            'user' => $request->user(),
            'trips' => $trips,
        ]);
    }

    /**
     * Show the user's bookings (as traveler).
     */
    public function bookings(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('trip:id,origin_name,destination_name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'trip' => [
                    'origin_name' => $booking->trip?->origin_name,
                    'destination_name' => $booking->trip?->destination_name,
                ],
                'seat_count' => $booking->seat_count,
                'status' => $booking->status,
                'created_at' => $booking->created_at,
            ]);

        return Inertia::render('portal/bookings', [
            'user' => $request->user(),
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show the user's wallet and transaction history.
     */
    public function wallet(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;
        $transactions = collect();

        if ($wallet) {
            $transactions = $wallet->transactions()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($tx) => [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'direction' => $tx->direction,
                    'amount' => $tx->amount,
                    'created_at' => $tx->created_at,
                ]);
        }

        return Inertia::render('portal/wallet', [
            'user' => $user,
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the user's profile edit form.
     */
    public function profile(Request $request)
    {
        return Inertia::render('portal/profile', [
            'user' => $request->user(),
            'success' => session('success'),
        ]);
    }
}
