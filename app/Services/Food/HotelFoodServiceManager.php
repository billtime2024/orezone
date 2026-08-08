<?php

namespace App\Services\Food;

use App\Models\Food\FoodItem;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodOrderStatusHistory;
use App\Models\Food\FoodPricingTier;
use App\Models\Food\HotelFoodService;
use App\Models\Food\HotelReservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HotelFoodServiceManager
{
    public function __construct(
        private readonly FoodOrderService $orderService,
        private readonly FoodCommissionService $commissionService,
    ) {}

    /**
     * Get the food menu for a hotel provider.
     *
     * @param int $providerId
     * @return \Illuminate\Support\Collection
     */
    public function getHotelMenu(int $providerId): Collection
    {
        return FoodItem::where('provider_id', $providerId)
            ->where('is_available', true)
            ->where('status', 'active')
            ->with(['pricingTiers', 'category:id,name'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a hotel food reservation (dine-in, banquet, etc.).
     *
     * @param int   $userId
     * @param int   $serviceId   The hotel food service ID
     * @param array $data  [reservation_date, reservation_time, guest_count, special_requests]
     * @return \App\Models\Food\HotelReservation
     *
     * @throws \InvalidArgumentException
     */
    public function createReservation(int $userId, int $serviceId, array $data): HotelReservation
    {
        return DB::transaction(function () use ($userId, $serviceId, $data) {
            // Validate hotel food service exists
            $hotelService = HotelFoodService::where('id', $serviceId)
                ->where('is_active', true)
                ->first();

            if (!$hotelService) {
                throw new InvalidArgumentException('Hotel food service not found.');
            }

            // Check for conflicting reservations with row lock
            $conflict = HotelReservation::where('hotel_service_id', $serviceId)
                ->where('reservation_date', $data['reservation_date'])
                ->where('reservation_time', $data['reservation_time'])
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->count();

            $maxCapacity = (int) ($hotelService->capacity ?? 100);

            if ($conflict >= $maxCapacity) {
                throw new InvalidArgumentException('No availability for the selected date and time.');
            }

            return HotelReservation::create([
                'user_id'             => $userId,
                'hotel_service_id'    => $serviceId,
                'reservation_date'    => $data['reservation_date'],
                'reservation_time'    => $data['reservation_time'],
                'party_size'          => $data['guest_count'],
                'special_requests'    => $data['special_requests'] ?? null,
                'status'              => HotelReservation::STATUS_CONFIRMED,
            ]);
        });
    }

    /**
     * Update a hotel reservation status.
     *
     * @param int    $reservationId
     * @param string $status
     * @return \App\Models\Food\HotelReservation
     *
     * @throws \InvalidArgumentException
     */
    public function updateReservationStatus(int $reservationId, string $status, ?int $providerId = null): HotelReservation
    {
        $validStatuses = [
            HotelReservation::STATUS_CONFIRMED,
            HotelReservation::STATUS_SEATED,
            HotelReservation::STATUS_COMPLETED,
            HotelReservation::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid reservation status: {$status}");
        }

        $reservation = HotelReservation::with('hotelService')->findOrFail($reservationId);

        // Authorization: if providerId is provided, verify the reservation belongs to that provider
        if ($providerId !== null) {
            if (!$reservation->hotelService || $reservation->hotelService->provider_id !== $providerId) {
                abort(403, 'This reservation does not belong to your provider account.');
            }
        }

        $reservation->update(['status' => $status]);

        return $reservation->fresh();
    }

    /**
     * Order room service from a hotel.
     *
     * Creates a food order for delivery to the guest's room.
     *
     * @param int   $userId
     * @param int   $serviceId  Hotel food provider ID
     * @param array $items      [{food_item_id, pricing_tier_id, quantity, notes}, ...]
     * @return \App\Models\Food\FoodOrder
     *
     * @throws \InvalidArgumentException
     */
    public function orderRoomService(int $userId, int $serviceId, array $items): FoodOrder
    {
        return DB::transaction(function () use ($userId, $serviceId, $items) {
            $provider = \App\Models\Food\FoodProvider::where('id', $serviceId)
                ->where('provider_type', 'hotel')
                ->first();

            if (!$provider) {
                throw new InvalidArgumentException('Hotel provider not found.');
            }

            // Validate and calculate items
            $subtotal = 0.0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $foodItem = FoodItem::where('id', $item['food_item_id'])
                    ->where('provider_id', $serviceId)
                    ->where('is_available', true)
                    ->first();

                if (!$foodItem) {
                    throw new InvalidArgumentException(
                        "Food item #{$item['food_item_id']} not found or unavailable."
                    );
                }

                $tier = null;
                if (!empty($item['pricing_tier_id'])) {
                    $tier = FoodPricingTier::where('id', $item['pricing_tier_id'])
                        ->where('food_item_id', $foodItem->id)
                        ->first();

                    if (!$tier) {
                        throw new InvalidArgumentException('Invalid pricing tier.');
                    }
                }

                $price = $tier ? (float) $tier->price : (float) $foodItem->price;
                $qty = (int) ($item['quantity'] ?? 1);
                $itemTotal = round($price * $qty, 2);
                $subtotal += $itemTotal;

                $orderItemsData[] = [
                    'food_item_id'         => $foodItem->id,
                    'pricing_tier_id'      => $tier?->id,
                    'quantity'              => $qty,
                    'price'                 => $price,
                    'total'                 => $itemTotal,
                    'special_notes'         => $item['notes'] ?? null,
                ];
            }

            $taxRate = config('food.tax_rate', 0.05);
            $tax = round($subtotal * $taxRate, 2);
            $grandTotal = round($subtotal + $tax, 2);

            // Calculate commission
            $commissionRate = (float) $provider->commission_rate;
            $commission = $this->commissionService->calculateCommission($subtotal, $commissionRate);

            $order = FoodOrder::create([
                'order_number'      => $this->orderService->generateOrderNumber(),
                'user_id'           => $userId,
                'provider_id'       => $serviceId,
                'subtotal'          => round($subtotal, 2),
                'tax_amount'        => $tax,
                'delivery_charge'   => 0, // Room service has no delivery charge
                'discount_amount'   => 0,
                'total_amount'      => $grandTotal,
                'commission_amount' => $commission,
                'payment_method'    => 'room_charge',
                'order_type'        => 'room_service',
                'special_instructions' => 'Room service order',
                'status'            => FoodOrder::STATUS_CONFIRMED,
                'payment_status'    => 'pending',
            ]);

            foreach ($orderItemsData as $itemData) {
                FoodOrderItem::create(array_merge($itemData, [
                    'food_order_id' => $order->id,
                ]));
            }

            FoodOrderStatusHistory::create([
                'food_order_id' => $order->id,
                'status'        => FoodOrder::STATUS_CONFIRMED,
                'changed_by'    => $userId,
                'notes'         => 'Room service order placed',
            ]);

            return $order->fresh(['items.foodItem', 'provider']);
        });
    }
}
