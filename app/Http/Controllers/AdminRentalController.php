<?php

namespace App\Http\Controllers;

use App\Models\RentalListing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRentalController extends Controller
{
    /**
     * GET /admin/rentals — List all rental listings.
     */
    public function index(Request $request)
    {
        $query = RentalListing::with(['owner:id,name,email', 'houseDetails', 'carDetails', 'commercialDetails', 'roomDetails']);

        if ($request->filled('rental_type')) {
            $query->where('rental_type', $request->rental_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address_line1', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $listings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/rentals/index', ['listings' => $listings]);
    }

    /**
     * GET /admin/rentals/{listing} — Show listing details.
     */
    public function show(RentalListing $listing)
    {
        $listing->load([
            'owner:id,name,email,phone',
            'houseDetails', 'carDetails', 'commercialDetails', 'roomDetails',
            'bookings' => function ($q) {
                $q->with('guest:id,name,email')->latest()->limit(10);
            },
            'reviews' => function ($q) {
                $q->with('user:id,name')->latest()->limit(10);
            },
        ]);

        $stats = $listing->bookings()
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_bookings,
                SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as total_revenue
            ', ['active', 'completed'])
            ->first();
        $stats = [
            'total_bookings' => $stats->total_bookings,
            'active_bookings' => $stats->active_bookings,
            'total_revenue' => $stats->total_revenue,
            'avg_rating' => $listing->avg_rating,
            'review_count' => $listing->review_count,
        ];

        return Inertia::render('admin/rentals/show', [
            'listing' => $listing,
            'stats' => $stats,
        ]);
    }
}
