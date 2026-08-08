<?php

namespace App\Services\Food;

use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use Illuminate\Support\Facades\DB;

class FoodCommissionService
{
    /**
     * Calculate commission based on subtotal and commission rate.
     *
     * @param float $subtotal
     * @param float $commissionRate  e.g. 15 for 15%
     * @return float
     */
    public function calculateCommission(float $subtotal, float $commissionRate): float
    {
        return round($subtotal * ($commissionRate / 100), 2);
    }

    /**
     * Get earnings summary for a specific provider.
     *
     * @param int $providerId
     * @return array{
     *     total_orders: int,
     *     total_revenue: float,
     *     total_commission: float,
     *     net_earnings: float,
     *     avg_order_value: float,
     *     pending_payout: float,
     * }
     */
    public function getProviderEarnings(int $providerId): array
    {
        $stats = FoodOrder::where('provider_id', $providerId)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(subtotal), 0) as total_revenue,
                COALESCE(SUM(commission_amount), 0) as total_commission,
                COALESCE(AVG(subtotal), 0) as avg_order_value
            ')
            ->first();

        $pendingPayout = FoodOrder::where('provider_id', $providerId)
            ->where('status', 'delivered')
            ->where('payout_status', '!=', 'paid')
            ->sum('subtotal');

        $netEarnings = (float) $stats->total_revenue - (float) $stats->total_commission;

        return [
            'total_orders'    => (int) $stats->total_orders,
            'total_revenue'   => round((float) $stats->total_revenue, 2),
            'total_commission' => round((float) $stats->total_commission, 2),
            'net_earnings'    => round($netEarnings, 2),
            'avg_order_value' => round((float) $stats->avg_order_value, 2),
            'pending_payout'  => round((float) $pendingPayout, 2),
        ];
    }

    /**
     * Get platform-wide earnings summary.
     *
     * @param array $filters  [date_from, date_to]
     * @return array{
     *     total_orders: int,
     *     total_revenue: float,
     *     total_commission: float,
     *     avg_commission_rate: float,
     *     active_providers: int,
     *     top_earners: \Illuminate\Support\Collection,
     * }
     */
    public function getPlatformEarnings(array $filters = []): array
    {
        $query = FoodOrder::query()
            ->where('status', '!=', 'cancelled');

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $stats = $query->clone()->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(subtotal), 0) as total_revenue,
            COALESCE(SUM(commission_amount), 0) as total_commission
        ')->first();

        // Calculate average commission rate from providers
        $avgCommissionRate = FoodProvider::where('is_active', true)
            ->avg('commission_rate') ?? 0;

        $activeProviders = FoodProvider::where('is_active', true)->count();

        // Top earning providers
        $topEarners = FoodOrder::where('status', '!=', 'cancelled')
            ->select('provider_id', DB::raw('SUM(commission_amount) as total_commission'))
            ->groupBy('provider_id')
            ->orderByDesc('total_commission')
            ->limit(10)
            ->with(['provider:id,name,slug,logo_url'])
            ->get();

        return [
            'total_orders'       => (int) $stats->total_orders,
            'total_revenue'      => round((float) $stats->total_revenue, 2),
            'total_commission'   => round((float) $stats->total_commission, 2),
            'avg_commission_rate' => round((float) $avgCommissionRate, 4),
            'active_providers'   => $activeProviders,
            'top_earners'        => $topEarners,
        ];
    }

    /**
     * Record commission entry for an order.
     *
     * This is called during order creation to log the commission
     * for financial tracking.
     *
     * @param \App\Models\FoodOrder $order
     * @return void
     */
    public function recordCommission(FoodOrder $order): void
    {
        if ((float) $order->commission_amount <= 0) {
            return;
        }

        // Get commission rate from provider
        $provider = $order->provider;
        $commissionRate = $provider ? (float) $provider->commission_rate : 0;

        // Record in a commission ledger (table: food_commission_entries)
        DB::table('food_commission_entries')->insert([
            'food_order_id'     => $order->id,
            'food_provider_id'  => $order->provider_id,
            'subtotal'          => $order->subtotal,
            'commission_rate'   => $commissionRate,
            'commission_amount' => $order->commission_amount,
            'status'            => 'pending',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
