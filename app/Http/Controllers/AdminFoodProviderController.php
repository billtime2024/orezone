<?php

namespace App\Http\Controllers;

use App\Models\Food\FoodItem;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Models\Food\FoodReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFoodProviderController extends Controller
{
    /**
     * GET /admin/food-providers/create — Show create form.
     */
    public function create()
    {
        return Inertia::render('admin/food/providers/create', [
            'users' => \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    /**
     * POST /admin/food-providers — Store a new food provider.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'            => 'required|exists:users,id',
            'provider_type'      => 'required|in:homemade,catering,hotel',
            'business_name'      => 'required|string|max:255',
            'description'        => 'nullable|string',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'city'               => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'pincode'            => 'nullable|string|max:10',
            'fssai_license'      => 'nullable|string|max:50',
            'fssai_expiry'       => 'nullable|date',
            'gst_number'         => 'nullable|string|max:20',
            'pan_number'         => 'nullable|string|max:20',
            'bank_account_number'=> 'nullable|string|max:30',
            'bank_ifsc'          => 'nullable|string|max:20',
            'upi_id'             => 'nullable|string|max:100',
            'commission_rate'    => 'nullable|numeric|min:0|max:100',
            'delivery_radius_km' => 'nullable|integer|min:0',
            'min_order_amount'   => 'nullable|numeric|min:0',
        ]);

        $validated['verification_status'] = 'pending';
        $validated['is_active'] = true;
        $validated['is_featured'] = false;

        $validated['commission_rate'] = $validated['commission_rate'] ?? 10;
        $validated['delivery_radius_km'] = $validated['delivery_radius_km'] ?? 5;
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;

        FoodProvider::create($validated);

        return redirect()->route('admin.food-providers')
            ->with('success', 'Food provider created successfully.');
    }


    /**
     * GET /admin/food/providers — List all food providers.
     */
    public function index(Request $request)
    {
        $query = FoodProvider::with(['user:id,name,email,phone']);

        if ($request->filled('provider_type')) {
            $query->where('provider_type', $request->provider_type);
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $providers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/food/providers/index', [
            'providers' => $providers,
            'filters' => $request->only(['provider_type', 'status', 'is_active', 'search']),
        ]);
    }

    /**
     * GET /admin/food/providers/{provider} — Show provider details.
     */
    public function show(FoodProvider $provider)
    {
        $provider->load([
            'user:id,name,email,phone',
            'items' => function ($q) {
                $q->with('category:id,name')->latest()->limit(20);
            },
            'orders' => function ($q) {
                $q->with('user:id,name,email')->latest()->limit(20);
            },
            'reviews' => function ($q) {
                $q->with('user:id,name')->latest()->limit(20);
            },
        ]);

        $stats = $provider->orders()
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_orders,
                SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = ? THEN commission_amount ELSE 0 END) as total_commission
            ', ['delivered', 'delivered', 'delivered'])
            ->first();

        $stats = [
            'total_orders' => $stats->total_orders ?? 0,
            'active_orders' => $stats->active_orders ?? 0,
            'total_revenue' => $stats->total_revenue ?? 0,
            'total_commission' => $stats->total_commission ?? 0,
            'avg_rating' => $provider->avg_rating,
            'review_count' => $provider->reviews()->count(),
            'total_items' => $provider->items()->count(),
        ];

        return Inertia::render('admin/food/providers/show', [
            'provider' => $provider,
            'stats' => $stats,
        ]);
    }

    /**
     * POST /admin/food/providers/{provider}/verify — Approve/reject provider.
     */
    public function verify(Request $request, FoodProvider $provider)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $provider->update([
            'verification_status' => $validated['status'],
            'verified_at' => $validated['status'] === 'approved' ? now() : null,
        ]);

        return back()->with('success', 'Provider verification ' . $validated['status'] . ' successfully.');
    }

    /**
     * POST /admin/food/providers/{provider}/toggle-featured — Toggle featured status.
     */
    public function toggleFeatured(FoodProvider $provider)
    {
        $provider->update([
            'is_featured' => !$provider->is_featured,
        ]);

        $message = $provider->is_featured ? 'Provider featured successfully.' : 'Provider unfeatured successfully.';

        return back()->with('success', $message);
    }

    /**
     * POST /admin/food/providers/{provider}/toggle-active — Toggle active status.
     */
    public function toggleActive(FoodProvider $provider)
    {
        $provider->update([
            'is_active' => !$provider->is_active,
        ]);

        $message = $provider->is_active ? 'Provider activated successfully.' : 'Provider deactivated successfully.';

        return back()->with('success', $message);
    }

    /**
     * GET /admin/food-providers/{provider}/edit — Show edit form.
     */
    public function edit(FoodProvider $provider)
    {
        return Inertia::render('admin/food/providers/edit', [
            'provider' => $provider,
            'users' => \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    /**
     * PUT /admin/food-providers/{provider} — Update food provider.
     */
    public function update(Request $request, FoodProvider $provider)
    {
        $validated = $request->validate([
            'provider_type'      => 'required|in:homemade,catering,hotel',
            'business_name'      => 'required|string|max:255',
            'description'        => 'nullable|string',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'city'               => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'pincode'            => 'nullable|string|max:10',
            'fssai_license'      => 'nullable|string|max:50',
            'fssai_expiry'       => 'nullable|date',
            'gst_number'         => 'nullable|string|max:20',
            'pan_number'         => 'nullable|string|max:20',
            'bank_account_number'=> 'nullable|string|max:30',
            'bank_ifsc'          => 'nullable|string|max:20',
            'upi_id'             => 'nullable|string|max:100',
            'commission_rate'    => 'nullable|numeric|min:0|max:100',
            'delivery_radius_km' => 'nullable|integer|min:0',
            'min_order_amount'   => 'nullable|numeric|min:0',
        ]);

        $provider->update($validated);

        return redirect()->route('admin.food-providers.show', $provider)
            ->with('success', 'Food provider updated successfully.');
    }

    /**
     * DELETE /admin/food-providers/{provider} — Delete food provider.
     */
    public function destroy(FoodProvider $provider)
    {
        $provider->delete();

        return redirect()->route('admin.food-providers')
            ->with('success', 'Food provider deleted successfully.');
    }

    /**
     * POST /admin/food-providers/{provider}/login-as — Login as provider (impersonate).
     */
    public function loginAs(FoodProvider $provider)
    {
        \Illuminate\Support\Facades\Auth::login($provider->user);
        return redirect('/portal');
    }
}
