<?php

namespace App\Http\Controllers;

use App\Models\Food\CateringRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFoodCateringController extends Controller
{
    /**
     * GET /admin/food/catering — List all catering requests.
     */
    public function index(Request $request)
    {
        $query = CateringRequest::with([
            'user:id,name,email,phone',
            'provider:id,business_name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('event_name', 'like', "%{$search}%")
                  ->orWhere('venue_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/food/catering/index', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'event_type', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * GET /admin/food/catering/{request} — Show catering request details.
     */
    public function show(CateringRequest $request)
    {
        $request->load([
            'user:id,name,email,phone',
            'provider:id,business_name,city,phone,email',
            'quotes' => function ($q) {
                $q->with('provider:id,business_name,city,avg_rating');
            },
        ]);

        $stats = [
            'total_quotes' => $request->quotes()->count(),
            'pending_quotes' => $request->quotes()->pending()->count(),
            'accepted_quote' => $request->quotes()->accepted()->first(),
            'total_budget' => $request->totalBudget(),
        ];

        return Inertia::render('admin/food/catering/show', [
            'request' => $request,
            'stats' => $stats,
        ]);
    }
}
