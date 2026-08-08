<?php

namespace App\Http\Controllers;

use App\Models\Food\FoodCategory;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Models\Food\CateringRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalFoodProviderController extends Controller
{
    /**
     * Resolve the authenticated user's FoodProvider profile.
     */
    private function getProvider(Request $request): FoodProvider
    {
        $provider = FoodProvider::where('user_id', $request->user()->id)->first();

        if (!$provider) {
            abort(403, 'You do not have a food provider profile. Please complete registration first.');
        }

        return $provider;
    }

    // ── Dashboard ─────────────────────────────────────────────────

    /**
     * GET /portal/food-provider — Provider dashboard.
     */
    public function index(Request $request)
    {
        $provider = $this->getProvider($request);

        $stats = [
            'total_items' => $provider->items()->count(),
            'active_items' => $provider->items()->where('is_available', true)->count(),
            'total_orders' => $provider->orders()->count(),
            'pending_orders' => $provider->orders()->where('status', 'placed')->count(),
            'active_orders' => $provider->orders()->whereIn('status', ['confirmed', 'preparing', 'ready', 'out_for_delivery'])->count(),
            'total_revenue' => $provider->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'today_orders' => $provider->orders()->whereDate('created_at', today())->count(),
            'today_revenue' => $provider->orders()->whereDate('created_at', today())->where('payment_status', 'paid')->sum('total_amount'),
            'avg_rating' => $provider->avg_rating,
            'catering_requests' => CateringRequest::where('provider_id', $provider->id)->count(),
            'pending_catering' => CateringRequest::where('provider_id', $provider->id)->where('status', 'pending')->count(),
        ];

        $recentOrders = $provider->orders()
            ->with('user:id,name,phone')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('portal/food-provider/index', [
            'provider' => $provider,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }

    // ── Menu Management ───────────────────────────────────────────

    /**
     * GET /portal/food-provider/menu — List all food items.
     */
    public function menu(Request $request)
    {
        $provider = $this->getProvider($request);

        $items = $provider->items()
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('portal/food-provider/menu', [
            'provider' => $provider,
            'items' => $items,
        ]);
    }

    /**
     * GET /portal/food-provider/menu/create — Create food item form.
     */
    public function createItem(Request $request)
    {
        $provider = $this->getProvider($request);

        $categories = FoodCategory::active()->root()->ordered()->get();

        return Inertia::render('portal/food-provider/menu-create', [
            'provider' => $provider,
            'categories' => $categories,
        ]);
    }

    /**
     * GET /portal/food-provider/menu/{item}/edit — Edit food item form.
     */
    public function editItem(Request $request, FoodItem $item)
    {
        $provider = $this->getProvider($request);

        if ($item->provider_id !== $provider->id) {
            abort(403);
        }

        $item->load('category:id,name');

        $categories = FoodCategory::active()->root()->ordered()->get();

        return Inertia::render('portal/food-provider/menu-edit', [
            'provider' => $provider,
            'item' => $item,
            'categories' => $categories,
        ]);
    }

    // ── Orders ────────────────────────────────────────────────────

    /**
     * GET /portal/food-provider/orders — List orders.
     */
    public function orders(Request $request)
    {
        $provider = $this->getProvider($request);

        $query = $provider->orders()->with('user:id,name,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(20);

        return Inertia::render('portal/food-provider/orders', [
            'provider' => $provider,
            'orders' => $orders,
            'statusFilter' => $request->get('status'),
        ]);
    }

    /**
     * GET /portal/food-provider/orders/{order} — Show single order.
     */
    public function order(Request $request, FoodOrder $order)
    {
        $provider = $this->getProvider($request);

        if ($order->provider_id !== $provider->id) {
            abort(403);
        }

        $order->load([
            'user:id,name,phone,email',
            'items.foodItem:id,name,image_url,price',
            'items',
        ]);

        return Inertia::render('portal/food-provider/order', [
            'provider' => $provider,
            'order' => $order,
        ]);
    }

    // ── Catering ──────────────────────────────────────────────────

    /**
     * GET /portal/food-provider/catering — List catering requests.
     */
    public function catering(Request $request)
    {
        $provider = $this->getProvider($request);

        $query = CateringRequest::where('provider_id', $provider->id)
            ->with('user:id,name,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('event_date')->paginate(20);

        return Inertia::render('portal/food-provider/catering', [
            'provider' => $provider,
            'cateringRequests' => $requests,
            'statusFilter' => $request->get('status'),
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────

    /**
     * GET /portal/food-provider/profile — Show provider profile.
     */
    public function profile(Request $request)
    {
        $provider = $this->getProvider($request);

        return Inertia::render('portal/food-provider/profile', [
            'provider' => $provider,
        ]);
    }
}
