<?php

namespace App\Http\Controllers\Api\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodReview;
use App\Services\Food\FoodOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodOrderController extends Controller
{
    public function __construct(
        private readonly FoodOrderService $orderService,
    ) {}

    /**
     * POST /food/orders — Place an order from the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method'   => 'nullable|in:cod,wallet,online',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_lat'     => 'nullable|numeric|between:-90,90',
            'delivery_lng'     => 'nullable|numeric|between:-180,180',
            'delivery_charge'  => 'nullable|numeric|min:0',
            'tax_rate'         => 'nullable|numeric|min:0|max:1',
            'discount'         => 'nullable|numeric|min:0',
            'coupon_code'      => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
        ]);

        $userId = $request->user()->id;

        $order = $this->orderService->placeOrder($userId, $validated);

        return response()->json([
            'success' => true,
            'data'    => $order,
        ], 201);
    }

    /**
     * GET /food/orders — User's order history.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'   => 'nullable|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $userId = $request->user()->id;
        $orders = $this->orderService->getUserOrders($userId, $validated);

        return response()->json([
            'success' => true,
            'data'    => $orders->getCollection(),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * GET /food/orders/{id} — Order detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $order = FoodOrder::where('id', $id)
            ->where('user_id', $userId)
            ->with([
                'items.foodItem',
                'items.pricingTier',
                'provider:id,business_name,logo_url,phone',
                'statusHistory' => function ($q) {
                    $q->with('changer:id,name,avatar')->latest();
                },
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * POST /food/orders/{id}/cancel — Cancel an order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $userId = $request->user()->id;
        $order = $this->orderService->cancelOrder($id, $validated['reason'], $userId);

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * POST /food/orders/{id}/rate — Rate a delivered order.
     */
    public function rate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = $request->user()->id;

        $order = FoodOrder::where('id', $id)
            ->where('user_id', $userId)
            ->where('status', 'delivered')
            ->firstOrFail();

        // Prevent duplicate ratings
        $existingRating = FoodReview::where('food_order_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'error'   => 'You have already rated this order.',
            ], 422);
        }

        $review = FoodReview::create([
            'food_order_id' => $id,
            'provider_id'   => $order->provider_id,
            'user_id'       => $userId,
            'rating'        => $validated['rating'],
            'comment'       => $validated['comment'] ?? null,
        ]);

        // Update provider's average rating
        $avgRating = FoodReview::where('provider_id', $order->provider_id)
            ->avg('rating');

        $order->provider->update(['avg_rating' => round($avgRating, 2)]);

        return response()->json([
            'success' => true,
            'data'    => $review,
        ], 201);
    }
}
