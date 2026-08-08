<?php

namespace App\Http\Controllers\Api\Food\Provider;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Services\Food\FoodCommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderDashboardController extends Controller
{
    public function __construct(
        private readonly FoodCommissionService $commissionService,
    ) {}

    /**
     * GET /food/provider/dashboard — Provider dashboard stats.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $this->getProvider($request);

        // Total orders
        $totalOrders = FoodOrder::where('provider_id', $provider->id)->count();

        // Pending orders (need action)
        $pendingOrders = FoodOrder::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // Today's orders
        $todayOrders = FoodOrder::where('provider_id', $provider->id)
            ->whereDate('created_at', today())
            ->count();

        // Revenue stats
        $earnings = $this->commissionService->getProviderEarnings($provider->id);

        // Recent orders
        $recentOrders = FoodOrder::where('provider_id', $provider->id)
            ->with(['user:id,name', 'items.foodItem:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        // Orders by status
        $ordersByStatus = FoodOrder::where('provider_id', $provider->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'success' => true,
            'data'    => [
                'provider'         => $provider,
                'total_orders'     => $totalOrders,
                'pending_orders'   => $pendingOrders,
                'today_orders'     => $todayOrders,
                'earnings'         => $earnings,
                'recent_orders'    => $recentOrders,
                'orders_by_status' => $ordersByStatus,
            ],
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
