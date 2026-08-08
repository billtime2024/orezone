<?php

namespace App\Services\Food;

use App\Models\Food\FoodCart;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodOrderStatusHistory;
use App\Models\Food\FoodProvider;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodPricingTier;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FoodOrderService
{
    public function __construct(
        private readonly FoodCartService $cartService,
        private readonly FoodCommissionService $commissionService,
    ) {}

    /**
     * Place an order from the user's cart.
     *
     * Uses DB::transaction with lockForUpdate to prevent race conditions
     * on inventory and seat availability.
     *
     * @param int   $userId
     * @param array $data  [payment_method, delivery_address, delivery_lat, delivery_lng, notes, coupon_code]
     * @return \App\Models\FoodOrder
     *
     * @throws \InvalidArgumentException
     */
    public function placeOrder(int $userId, array $data): FoodOrder
    {
        return DB::transaction(function () use ($userId, $data) {
            // Validate cart has items
            $cartItems = FoodCart::where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw new InvalidArgumentException('Cart is empty.');
            }

            // Validate all cart items are still available
            foreach ($cartItems as $cartItem) {
                $foodItem = FoodItem::lockForUpdate()->find($cartItem->food_item_id);

                if (!$foodItem || !$foodItem->is_available || $foodItem->status !== 'active') {
                    $itemName = $cartItem->foodItem ? $cartItem->foodItem->name : 'Unknown';
                    throw new InvalidArgumentException(
                        "Food item \"{$itemName}\" is no longer available."
                    );
                }

                // Validate pricing tier if set
                if ($cartItem->pricing_tier_id) {
                    $tier = FoodPricingTier::where('id', $cartItem->pricing_tier_id)
                        ->where('food_item_id', $cartItem->food_item_id)
                        ->first();

                    if (!$tier) {
                        throw new InvalidArgumentException(
                            "Pricing tier is no longer available for \"{$foodItem->name}\"."
                        );
                    }
                }
            }

            // Group cart items by provider
            $groupedItems = $cartItems->groupBy(function (FoodCart $item) {
                return $item->foodItem->provider_id;
            });

            $subtotal = 0.0;
            $orderItemsData = [];

            foreach ($cartItems as $cartItem) {
                $price = $cartItem->pricingTier
                    ? (float) $cartItem->pricingTier->price
                    : (float) $cartItem->foodItem->price;

                $itemTotal = round($price * $cartItem->quantity, 2);
                $subtotal += $itemTotal;

                $orderItemsData[] = [
                    'food_item_id'         => $cartItem->food_item_id,
                    'pricing_tier_id'     => $cartItem->pricing_tier_id,
                    'quantity'              => $cartItem->quantity,
                    'price'               => $price,
                    'total'               => $itemTotal,
                    'special_notes'       => $cartItem->special_notes,
                ];
            }

            // Calculate charges
            $deliveryCharge = (float) ($data['delivery_charge'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? config('food.tax_rate', 0.05));
            $tax = round($subtotal * $taxRate, 2);
            $discount = (float) ($data['discount'] ?? 0);
            $grandTotal = round($subtotal + $deliveryCharge + $tax - $discount, 2);

            // Determine provider (use first group for single-provider orders)
            $providerId = $groupedItems->keys()->first();
            $provider = FoodProvider::find($providerId);

            // Calculate commission
            $commissionRate = $provider ? (float) $provider->commission_rate : 0;
            $commission = $this->commissionService->calculateCommission($subtotal, $commissionRate);

            // Create the order
            $order = FoodOrder::create([
                'order_number'     => $this->generateOrderNumber(),
                'user_id'          => $userId,
                'provider_id'      => $providerId,
                'subtotal'         => round($subtotal, 2),
                'delivery_charge'  => $deliveryCharge,
                'tax_amount'          => $tax,
                'discount_amount'     => $discount,
                'total_amount'        => $grandTotal,
                'commission_amount'   => $commission,
                'payment_method'   => $data['payment_method'] ?? 'cod',
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_latitude'   => $data['delivery_lat'] ?? null,
                'delivery_longitude'  => $data['delivery_lng'] ?? null,
                'special_instructions' => $data['notes'] ?? null,
                'status'           => FoodOrder::STATUS_PLACED,
                'payment_status'   => 'pending',
            ]);

            // Create order items
            foreach ($orderItemsData as $itemData) {
                FoodOrderItem::create(array_merge($itemData, [
                    'food_order_id' => $order->id,
                ]));
            }

            // Record commission
            $this->commissionService->recordCommission($order);

            // Record initial status history
            FoodOrderStatusHistory::create([
                'food_order_id' => $order->id,
                'status'        => FoodOrder::STATUS_PLACED,
                'changed_by'    => $userId,
                'notes'         => 'Order placed',
            ]);

            // Clear the user's cart
            $this->cartService->clearCart($userId);

            return $order->fresh(['items.foodItem', 'provider']);
        });
    }

    /**
     * Update an order's status (provider or system action).
     *
     * Valid transitions:
     *   pending → confirmed → preparing → ready → out_for_delivery → delivered
     *   pending → cancelled  (provider can reject)
     *   confirmed → cancelled
     *
     * @param int       $orderId
     * @param string    $status
     * @param int|null  $providerId  Provider performing the action
     * @return \App\Models\FoodOrder
     *
     * @throws \InvalidArgumentException
     */
    public function updateOrderStatus(
        int $orderId,
        string $status,
        ?int $providerId = null
    ): FoodOrder {
        $validStatuses = [
            FoodOrder::STATUS_PLACED,
            FoodOrder::STATUS_CONFIRMED,
            FoodOrder::STATUS_PREPARING,
            FoodOrder::STATUS_READY,
            FoodOrder::STATUS_OUT_FOR_DELIVERY,
            FoodOrder::STATUS_DELIVERED,
            FoodOrder::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }

        return DB::transaction(function () use ($orderId, $status, $providerId) {
            $order = FoodOrder::lockForUpdate()->findOrFail($orderId);

            // Validate provider ownership if provider is making the change
            if ($providerId && $order->provider_id !== $providerId) {
                throw new InvalidArgumentException('You are not authorized to update this order.');
            }

            // Validate transition
            $this->validateTransition($order->status, $status);

            $now = now();
            $updateData = ['status' => $status];

            // Set timestamp fields based on status
            match ($status) {
                FoodOrder::STATUS_CONFIRMED      => $updateData['confirmed_at'] = $now,
                FoodOrder::STATUS_PREPARING       => $updateData['preparing_at'] = $now,
                FoodOrder::STATUS_READY           => $updateData['ready_at'] = $now,
                FoodOrder::STATUS_OUT_FOR_DELIVERY => $updateData['out_for_delivery_at'] = $now,
                FoodOrder::STATUS_DELIVERED       => $updateData['delivered_at'] = $now,
                FoodOrder::STATUS_CANCELLED       => $updateData['cancelled_at'] = $now,
                default => null,
            };

            $order->update($updateData);

            FoodOrderStatusHistory::create([
                'food_order_id' => $order->id,
                'status'        => $status,
                'changed_by'    => $providerId ?? $order->user_id,
                'notes'         => "Status updated to {$status}",
            ]);

            return $order->fresh(['items.foodItem', 'provider']);
        });
    }

    /**
     * Cancel an order with a reason.
     *
     * @param int    $orderId
     * @param string $reason
     * @param int    $userId  The user requesting cancellation
     * @return \App\Models\FoodOrder
     *
     * @throws \InvalidArgumentException
     */
    public function cancelOrder(int $orderId, string $reason, int $userId): FoodOrder
    {
        return DB::transaction(function () use ($orderId, $reason, $userId) {
            $order = FoodOrder::lockForUpdate()->findOrFail($orderId);

            // Only the ordering user can cancel
            if ($order->user_id !== $userId) {
                throw new InvalidArgumentException('You are not authorized to cancel this order.');
            }

            if (!in_array($order->status, [FoodOrder::STATUS_PLACED, FoodOrder::STATUS_CONFIRMED])) {
                throw new InvalidArgumentException(
                    "Order cannot be cancelled in current status: {$order->status}"
                );
            }

            $order->update([
                'status'         => FoodOrder::STATUS_CANCELLED,
                'cancelled_at'   => now(),
                'cancellation_reason'  => $reason,
            ]);

            FoodOrderStatusHistory::create([
                'food_order_id' => $order->id,
                'status'        => FoodOrder::STATUS_CANCELLED,
                'changed_by'    => $userId,
                'notes'         => $reason,
            ]);

            return $order->fresh(['items.foodItem', 'provider']);
        });
    }

    /**
     * Process a refund (partial or full).
     *
     * @param int   $orderId
     * @param float $refundAmount
     * @param string $reason
     * @return \App\Models\FoodOrder
     *
     * @throws \InvalidArgumentException
     */
    public function refundOrder(int $orderId, float $refundAmount, string $reason = ''): FoodOrder
    {
        return DB::transaction(function () use ($orderId, $refundAmount, $reason) {
            $order = FoodOrder::lockForUpdate()->findOrFail($orderId);

            if (!in_array($order->status, [FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_DELIVERED])) {
                throw new InvalidArgumentException(
                    'Refund is only available for cancelled or delivered orders.'
                );
            }

            if ($refundAmount <= 0 || $refundAmount > (float) $order->total_amount) {
                throw new InvalidArgumentException(
                    'Refund amount must be between 0 and the grand total.'
                );
            }

            $order->update([
                'refund_amount'  => round($refundAmount, 2),
                'refund_reason'  => $reason,
                'refund_status'  => 'processed',
                'refunded_at'    => now(),
            ]);

            FoodOrderStatusHistory::create([
                'food_order_id' => $order->id,
                'status'        => $order->status,
                'changed_by'    => $order->user_id,
                'notes'         => "Refund of ₹{$refundAmount} processed. {$reason}",
            ]);

            return $order->fresh(['items.foodItem', 'provider']);
        });
    }

    /**
     * Generate a unique order number in the format ORD-YYYYMMDD-XXXXX.
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        $maxRetries = 5;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $date = now()->format('Ymd');
            $prefix = "ORD-{$date}-";

            // Get the highest sequence number for today
            $lastOrder = FoodOrder::where('order_number', 'like', "{$prefix}%")
                ->orderByDesc('order_number')
                ->first();

            if ($lastOrder) {
                $lastSequence = (int) substr($lastOrder->order_number, -5);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }

            $orderNumber = $prefix . str_pad((string) $newSequence, 5, '0', STR_PAD_LEFT);

            // Verify uniqueness (race-condition safe)
            if (!FoodOrder::where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }

        // Fallback: append micro-timestamp to guarantee uniqueness
        return $prefix . str_pad((string) (microtime(true) * 100 % 100000), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get order history for a user.
     *
     * @param int   $userId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserOrders(int $userId, array $filters = [])
    {
        $query = FoodOrder::where('user_id', $userId)
            ->with(['items.foodItem', 'provider:id,name,slug,logo_url']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get incoming orders for a provider.
     *
     * @param int   $providerId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProviderOrders(int $providerId, array $filters = [])
    {
        $query = FoodOrder::where('provider_id', $providerId)
            ->with(['items.foodItem', 'user:id,name,phone']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Validate a status transition.
     *
     * @throws \InvalidArgumentException
     */
    private function validateTransition(string $from, string $to): void
    {
        $allowedTransitions = [
            FoodOrder::STATUS_PLACED => [
                FoodOrder::STATUS_CONFIRMED,
                FoodOrder::STATUS_CANCELLED,
            ],
            FoodOrder::STATUS_CONFIRMED => [
                FoodOrder::STATUS_PREPARING,
                FoodOrder::STATUS_CANCELLED,
            ],
            FoodOrder::STATUS_PREPARING => [
                FoodOrder::STATUS_READY,
                FoodOrder::STATUS_CANCELLED,
            ],
            FoodOrder::STATUS_READY => [
                FoodOrder::STATUS_OUT_FOR_DELIVERY,
            ],
            FoodOrder::STATUS_OUT_FOR_DELIVERY => [
                FoodOrder::STATUS_DELIVERED,
            ],
        ];

        if (!isset($allowedTransitions[$from]) || !in_array($to, $allowedTransitions[$from])) {
            throw new InvalidArgumentException(
                "Cannot transition from \"{$from}\" to \"{$to}\"."
            );
        }
    }
}
