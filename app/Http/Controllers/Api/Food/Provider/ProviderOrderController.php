<?php

namespace App\Http\Controllers\Api\Food\Provider;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Services\Food\FoodOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderOrderController extends Controller
{
    public function __construct(
        private readonly FoodOrderService $orderService,
    ) {}

    /**
     * GET /food/provider/orders — Incoming orders for the provider.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'   => 'nullable|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $provider = $this->getProvider($request);

        $orders = $this->orderService->getProviderOrders($provider->id, $validated);

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
     * PUT /food/provider/orders/{id}/status — Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
        ]);

        $provider = $this->getProvider($request);

        $order = $this->orderService->updateOrderStatus(
            $id,
            $validated['status'],
            $provider->id
        );

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * GET /food/provider/orders/{id} — Order detail for provider.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $provider = $this->getProvider($request);

        $order = FoodOrder::where('id', $id)
            ->where('provider_id', $provider->id)
            ->with([
                'items.foodItem',
                'items.pricingTier',
                'user:id,name,phone',
                'statusHistory' => function ($q) {
                    $q->with('changer:id,name')->latest();
                },
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * Get the provider record for the authenticated user.
     */
    private function getProvider(Request $request): FoodProvider
    {
        return FoodProvider::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
