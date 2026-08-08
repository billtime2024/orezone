<?php

namespace App\Http\Controllers;

use App\Models\RentalBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRentalBookingController extends Controller
{
    /**
     * GET /admin/rentals-bookings — List all rental bookings.
     */
    public function index(Request $request)
    {
        $query = RentalBooking::with([
            'listing:id,title,city,rental_type,photos',
            'guest:id,name,email',
            'owner:id,name,email',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rental_type')) {
            $query->whereHas('listing', function ($q) use ($request) {
                $q->where('rental_type', $request->rental_type);
            });
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('listing', fn ($q2) => $q2->where('title', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/rental-bookings/index', ['bookings' => $bookings]);
    }

    /**
     * GET /admin/rentals-bookings/{booking} — Show booking details.
     */
    public function show(RentalBooking $booking)
    {
        $booking->load([
            'listing:id,title,city,rental_type,photos,address_line1,pincode',
            'listing.details',
            'guest:id,name,email,phone',
            'owner:id,name,email,phone',
            'statusHistory' => function ($q) {
                $q->with('changedByUser:id,name')->latest();
            },
        ]);

        return Inertia::render('admin/rental-bookings/show', ['booking' => $booking]);
    }
}
