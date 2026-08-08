<?php

namespace App\Http\Controllers;

use App\Models\Food\CateringRequest;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use App\Models\Food\FoodReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminFoodAnalyticsController extends Controller
{
    /**
     * GET /admin/food/analytics — Dashboard analytics.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // ── Core Metrics ──────────────────────────────────────────────
        $orderStats = FoodOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END) as total_revenue,
                SUM(commission_amount) as total_commission,
                AVG(total_amount) as avg_order_value
            ', [FoodOrder::STATUS_DELIVERED, FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_CANCELLED])
            ->first();

        // ── Today's Metrics ───────────────────────────────────────────
        $todayStats = FoodOrder::whereDate('created_at', now())
            ->selectRaw('
                COUNT(*) as orders_today,
                SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END) as revenue_today,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_today
            ', [FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_DELIVERED])
            ->first();

        // ── Provider Stats ────────────────────────────────────────────
        $totalProviders = FoodProvider::count();
        $activeProviders = FoodProvider::where('is_active', true)->count();
        $verifiedProviders = FoodProvider::where('verification_status', 'approved')->count();
        $pendingVerification = FoodProvider::where('verification_status', 'pending')->count();

        // ── Top Providers ─────────────────────────────────────────────
        $topProviders = FoodProvider::where('is_active', true)
            ->with(['user:id,name'])
            ->selectRaw('*, total_orders as order_count, total_revenue as revenue')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'business_name' => $p->business_name,
                'city' => $p->city,
                'avg_rating' => $p->avg_rating,
                'total_orders' => $p->total_orders,
                'total_revenue' => $p->total_revenue,
                'commission_rate' => $p->commission_rate,
                'owner_name' => $p->user->name ?? '-',
            ]);

        // ── Category Performance ──────────────────────────────────────
        $categoryPerformance = FoodCategory::withCount(['items as item_count'])
            ->where('is_active', true)
            ->orderByDesc('item_count')
            ->get()
            ->map(function ($cat) {
                $totalRevenue = FoodItem::where('category_id', $cat->id)
                    ->join('food_order_items', 'food_items.id', '=', 'food_order_items.food_item_id')
                    ->join('food_orders', 'food_order_items.food_order_id', '=', 'food_orders.id')
                    ->where('food_orders.status', FoodOrder::STATUS_DELIVERED)
                    ->sum('food_order_items.total');

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'item_count' => $cat->item_count,
                    'total_revenue' => $totalRevenue ?? 0,
                ];
            });

        // ── Recent Orders Trend ───────────────────────────────────────
        $ordersTrend = FoodOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END) as revenue
            ', [FoodOrder::STATUS_CANCELLED])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Catering Summary ──────────────────────────────────────────
        $cateringStats = CateringRequest::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed,
                SUM(total_amount) as total_value
            ', [CateringRequest::STATUS_CONFIRMED])
            ->first();

        // ── Review Summary ────────────────────────────────────────────
        $reviewStats = FoodReview::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                AVG(taste_rating) as avg_taste,
                AVG(packaging_rating) as avg_packaging,
                AVG(delivery_rating) as avg_delivery
            ')
            ->first();

        return Inertia::render('admin/food/analytics/index', [
            'stats' => [
                'total_orders' => $orderStats->total_orders ?? 0,
                'delivered_orders' => $orderStats->delivered_orders ?? 0,
                'cancelled_orders' => $orderStats->cancelled_orders ?? 0,
                'total_revenue' => $orderStats->total_revenue ?? 0,
                'total_commission' => $orderStats->total_commission ?? 0,
                'avg_order_value' => $orderStats->avg_order_value ?? 0,
                'orders_today' => $todayStats->orders_today ?? 0,
                'revenue_today' => $todayStats->revenue_today ?? 0,
                'delivered_today' => $todayStats->delivered_today ?? 0,
                'active_providers' => $activeProviders,
                'total_providers' => $totalProviders,
                'verified_providers' => $verifiedProviders,
                'pending_verification' => $pendingVerification,
                'top_providers' => $topProviders,
                'category_performance' => $categoryPerformance,
                'status_distribution' => $ordersTrend,
                'catering_stats' => $cateringStats,
                'review_stats' => $reviewStats,
            ],
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }

    /**
     * GET /admin/food/commissions — Commission reports by provider.
     */
    public function commissions(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $providers = FoodProvider::where('is_active', true)
            ->with(['user:id,name,email'])
            ->get()
            ->map(function ($provider) use ($dateFrom, $dateTo) {
                $orderStats = FoodOrder::where('provider_id', $provider->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                    ->selectRaw('
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as gross_revenue,
                        SUM(CASE WHEN status = ? THEN commission_amount ELSE 0 END) as platform_commission,
                        SUM(CASE WHEN status = ? THEN (total_amount - commission_amount) ELSE 0 END) as provider_payout
                    ', [FoodOrder::STATUS_DELIVERED, FoodOrder::STATUS_DELIVERED, FoodOrder::STATUS_DELIVERED])
                    ->first();

                $cateringRevenue = CateringRequest::where('provider_id', $provider->id)
                    ->where('status', CateringRequest::STATUS_CONFIRMED)
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                    ->sum('total_amount');

                $cateringCommission = $cateringRevenue * ($provider->commission_rate / 100);

                return [
                    'id' => $provider->id,
                    'business_name' => $provider->business_name,
                    'city' => $provider->city,
                    'commission_rate' => $provider->commission_rate,
                    'owner_name' => $provider->user->name ?? '-',
                    'food_orders' => [
                        'total_orders' => $orderStats->total_orders ?? 0,
                        'gross_revenue' => $orderStats->gross_revenue ?? 0,
                        'platform_commission' => $orderStats->platform_commission ?? 0,
                        'provider_payout' => $orderStats->provider_payout ?? 0,
                    ],
                    'catering' => [
                        'gross_revenue' => $cateringRevenue,
                        'platform_commission' => round($cateringCommission, 2),
                        'provider_payout' => round($cateringRevenue - $cateringCommission, 2),
                    ],
                    'total_platform_commission' => round(
                        ($orderStats->platform_commission ?? 0) + $cateringCommission, 2
                    ),
                ];
            })
            ->sortByDesc('total_platform_commission')
            ->values();

        $summary = [
            'total_providers' => $providers->count(),
            'total_food_revenue' => $providers->sum('food_orders.gross_revenue'),
            'total_catering_revenue' => $providers->sum('catering.gross_revenue'),
            'total_platform_commission' => $providers->sum('total_platform_commission'),
            'total_provider_payouts' => $providers->sum('food_orders.provider_payout')
                + $providers->sum('catering.provider_payout'),
        ];

        return Inertia::render('admin/food/commissions/index', [
            'commissions' => $providers,
            'summary' => $summary,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }
}
