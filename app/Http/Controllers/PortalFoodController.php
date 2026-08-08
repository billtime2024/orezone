<?php

namespace App\Http\Controllers;

use App\Models\Food\FoodItem;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Models\Food\FoodCart;
use App\Models\Food\FoodCategory;
use App\Models\Food\CateringRequest;
use App\Services\Food\FoodCartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalFoodController extends Controller
{
    /**
     * GET /portal/food — Browse food providers and featured items.
     */
    public function index(Request $request)
    {
        $categories = FoodCategory::active()->root()->ordered()->get();

        $providers = FoodProvider::active()
            ->verified()
            ->withCount('items')
            ->orderByDesc('is_featured')
            ->orderByDesc('avg_rating')
            ->paginate(12);

        $featuredItems = FoodItem::available()
            ->featured()
            ->with('provider:id,business_name,logo_url,city')
            ->limit(8)
            ->get();

        return Inertia::render('portal/food/index', [
            'providers' => $providers,
            'categories' => $categories,
            'featuredItems' => $featuredItems,
        ]);
    }

    /**
     * GET /portal/food/search — Search food items and providers.
     */
    public function search(Request $request)
    {
        $query = addcslashes($request->input('q', ''), '%_');
        $categoryId = $request->input('category');
        $city = $request->input('city');
        $sortBy = $request->input('sort', 'relevance');

        $itemsQuery = FoodItem::available()
            ->with('provider:id,business_name,logo_url,city')
            ->with('category:id,name');

        if ($query) {
            $itemsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($categoryId) {
            $itemsQuery->where('category_id', $categoryId);
        }

        if ($city) {
            $itemsQuery->whereHas('provider', fn ($q) => $q->where('city', $city));
        }

        match ($sortBy) {
            'price_low' => $itemsQuery->orderBy('price'),
            'price_high' => $itemsQuery->orderByDesc('price'),
            'rating' => $itemsQuery->orderByDesc('avg_rating'),
            'popular' => $itemsQuery->orderByDesc('total_orders'),
            default => $itemsQuery->orderByDesc('is_featured')->orderByDesc('avg_rating'),
        };

        $items = $itemsQuery->paginate(24)->withQueryString();

        $categories = FoodCategory::active()->root()->ordered()->get();

        return Inertia::render('portal/food/search', [
            'items' => $items,
            'categories' => $categories,
            'filters' => $request->only(['q', 'category', 'city', 'sort']),
        ]);
    }

    /**
     * GET /portal/food/provider/{provider} — Provider profile and menu.
     */
    public function provider(FoodProvider $provider)
    {
        $provider->loadCount('items', 'reviews');

        $items = FoodItem::where('provider_id', $provider->id)
            ->available()
            ->with('category:id,name')
            ->orderBy('category_id')
            ->get();

        $categories = $items->pluck('category')->filter()->unique('id')->values();

        $reviews = $provider->reviews()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('portal/food/provider', [
            'provider' => $provider,
            'items' => $items,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    }

    /**
     * GET /portal/food/item/{item} — Food item detail page.
     */
    public function item(FoodItem $item)
    {
        $item->load([
            'provider:id,business_name,logo_url,city,delivery_radius_km,min_order_amount,free_delivery_above',
            'category:id,name',
            'pricingTiers',
            'media',
        ]);

        $relatedItems = FoodItem::where('provider_id', $item->provider_id)
            ->where('id', '!=', $item->id)
            ->available()
            ->limit(6)
            ->get();

        return Inertia::render('portal/food/item', [
            'item' => $item,
            'relatedItems' => $relatedItems,
        ]);
    }

    /**
     * GET /portal/food/cart — View cart.
     */
    public function cart(Request $request)
    {
        $cartItems = FoodCart::where('user_id', $request->user()->id)
            ->with([
                'foodItem:id,name,price,discount_price,image_url,is_available,provider_id',
                'foodItem.provider:id,business_name',
                'pricingTier:id,name,price',
            ])
            ->get();

        $subtotal = $cartItems->sum(function ($cart) {
            $price = $cart->pricingTier
                ? $cart->pricingTier->price
                : $cart->foodItem->discount_price ?? $cart->foodItem->price;
            return round((float) $price * $cart->quantity, 2);
        });

        return Inertia::render('portal/food/cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * GET /portal/food/orders — Order history.
     */
    public function orders(Request $request)
    {
        $orders = FoodOrder::where('user_id', $request->user()->id)
            ->with('provider:id,business_name,logo_url')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('portal/food/orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * GET /portal/food/orders/{order} — Order detail.
     */
    public function order(FoodOrder $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'provider:id,business_name,logo_url,phone,email',
            'items.foodItem:id,name,image_url',
            'items.pricingTier:id,name',
        ]);

        return Inertia::render('portal/food/order', [
            'order' => $order,
        ]);
    }

    /**
     * GET /portal/food/catering — Catering request history.
     */
    public function catering(Request $request)
    {
        $requests = CateringRequest::where('user_id', $request->user()->id)
            ->with('provider:id,business_name')
            ->withCount('quotes')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('portal/food/catering', [
            'requests' => $requests,
        ]);
    }

    /**
     * GET /portal/food/catering/create — Create catering request form.
     */
    public function createCatering(Request $request)
    {
        $providers = FoodProvider::active()
            ->verified()
            ->select('id', 'business_name', 'logo_url', 'city')
            ->orderBy('business_name')
            ->get();

        return Inertia::render('portal/food/catering-create', [
            'providers' => $providers,
        ]);
    }

    /**
     * POST /portal/food/cart/add — Add item to cart.
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'food_item_id' => 'required|exists:food_items,id',
            'quantity' => 'required|integer|min:1|max:20',
            'pricing_tier_id' => 'nullable|exists:food_pricing_tiers,id',
            'special_notes' => 'nullable|string|max:500',
        ]);

        // H7: Verify the food item belongs to an active, verified provider
        $foodItem = FoodItem::where('id', $validated['food_item_id'])
            ->whereHas('provider', fn ($q) => $q->active()->verified())
            ->first();

        if (!$foodItem) {
            abort(422, 'Food item is not available from an active provider.');
        }

        // M9: Delegate to FoodCartService instead of duplicating cart logic
        $cartService = app(FoodCartService::class);
        $cartService->addToCart(
            userId: $request->user()->id,
            foodItemId: $validated['food_item_id'],
            pricingTierId: $validated['pricing_tier_id'] ?? null,
            quantity: $validated['quantity'],
            notes: $validated['special_notes'] ?? null,
        );

        return back()->with('success', 'Item added to cart!');
    }

    /**
     * PUT /portal/food/cart/{cartItem} — Update cart item quantity.
     */
    public function updateCartItem(Request $request, FoodCart $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:20',
            'special_notes' => 'nullable|string|max:500',
        ]);

        $cartItem->update($validated);

        return back()->with('success', 'Cart updated!');
    }

    /**
     * DELETE /portal/food/cart/{cartItem} — Remove item from cart.
     */
    public function removeCartItem(Request $request, FoodCart $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * POST /portal/food/catering — Submit catering request.
     */
    public function storeCatering(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|exists:food_providers,id',
            'event_type' => 'required|string|max:100',
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_time' => 'required|string|max:20',
            'venue_address' => 'required|string|max:500',
            'guest_count' => 'required|integer|min:1|max:50000',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'cuisine_preferences' => 'nullable|array',
            'dietary_requirements' => 'nullable|array',
            'menu_description' => 'nullable|string|max:2000',
            'special_requests' => 'nullable|string|max:2000',
            'tasting_requested' => 'nullable|boolean',
        ]);

        // M10: Only pass validated fields; set server-controlled values explicitly.
        // Do NOT allow total_amount, advance_paid, or payment_status from user input.
        CateringRequest::create([
            'user_id'               => $request->user()->id,
            'request_number'        => 'CR-' . strtoupper(uniqid()),
            'status'                => 'pending',
            'provider_id'           => $validated['provider_id'] ?? null,
            'event_type'            => $validated['event_type'],
            'event_name'            => $validated['event_name'],
            'event_date'            => $validated['event_date'],
            'event_end_date'        => $validated['event_end_date'] ?? null,
            'event_time'            => $validated['event_time'],
            'venue_address'         => $validated['venue_address'],
            'guest_count'           => $validated['guest_count'],
            'budget_min'            => $validated['budget_min'] ?? null,
            'budget_max'            => $validated['budget_max'] ?? null,
            'cuisine_preferences'   => $validated['cuisine_preferences'] ?? null,
            'dietary_requirements'  => $validated['dietary_requirements'] ?? null,
            'menu_description'      => $validated['menu_description'] ?? null,
            'special_requests'      => $validated['special_requests'] ?? null,
            'tasting_requested'     => $validated['tasting_requested'] ?? false,
        ]);

        return redirect()->route('portal.food.catering')
            ->with('success', 'Catering request submitted! Providers will send quotes shortly.');
    }
}
