<?php

namespace App\Services\Food;

use App\Models\Food\FoodCart;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodPricingTier;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FoodCartService
{
    /**
     * Get the user's cart items.
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getCart(int $userId): Collection
    {
        return FoodCart::where('user_id', $userId)
            ->with([
                'foodItem:id,name,slug,image_url,is_available,provider_id',
                'foodItem.provider:id,name,slug',
                'pricingTier:id,name,price,unit,food_item_id',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Add an item to the user's cart.
     *
     * If the same item + pricing tier already exists, increments quantity
     * instead of creating a duplicate row.
     *
     * @param int         $userId
     * @param int         $foodItemId
     * @param int|null    $pricingTierId
     * @param int         $quantity
     * @param string|null $notes
     * @return \App\Models\FoodCart
     *
     * @throws \InvalidArgumentException
     */
    public function addToCart(
        int $userId,
        int $foodItemId,
        ?int $pricingTierId = null,
        int $quantity = 1,
        ?string $notes = null
    ): FoodCart {
        // Validate food item exists and is available
        $foodItem = FoodItem::where('id', $foodItemId)
            ->where('is_available', true)
            ->where('status', 'active')
            ->first();

        if (!$foodItem) {
            throw new InvalidArgumentException('Food item not found or unavailable.');
        }

        // Validate pricing tier if provided
        if ($pricingTierId) {
            $tier = FoodPricingTier::where('id', $pricingTierId)
                ->where('food_item_id', $foodItemId)
                ->first();

            if (!$tier) {
                throw new InvalidArgumentException('Invalid pricing tier for this food item.');
            }
        }

        // Check for existing cart entry with same item + tier
        $existing = FoodCart::where('user_id', $userId)
            ->where('food_item_id', $foodItemId)
            ->where('pricing_tier_id', $pricingTierId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            if ($notes !== null) {
                $existing->update(['special_notes' => $notes]);
            }
            return $existing->fresh(['foodItem', 'pricingTier']);
        }

        return FoodCart::create([
            'user_id'             => $userId,
            'food_item_id'        => $foodItemId,
            'pricing_tier_id'     => $pricingTierId,
            'quantity'            => $quantity,
            'special_notes'       => $notes,
        ]);
    }

    /**
     * Update a cart item's quantity.
     *
     * @param int $cartItemId
     * @param int $quantity
     * @return \App\Models\FoodCart
     *
     * @throws \InvalidArgumentException
     */
    public function updateCartItem(int $cartItemId, int $quantity, int $userId): FoodCart
    {
        $cartItem = FoodCart::where('user_id', $userId)->findOrFail($cartItemId);

        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh(['foodItem', 'pricingTier']);
    }

    /**
     * Remove a single item from the cart.
     *
     * @param int $cartItemId
     * @return bool
     */
    public function removeFromCart(int $cartItemId, int $userId): bool
    {
        $cartItem = FoodCart::where('user_id', $userId)->find($cartItemId);

        if (!$cartItem) {
            return false;
        }

        return $cartItem->delete();
    }

    /**
     * Clear all items from the user's cart.
     *
     * @param int $userId
     * @return bool
     */
    public function clearCart(int $userId): bool
    {
        FoodCart::where('user_id', $userId)->delete();

        return true;
    }

    /**
     * Calculate cart totals.
     *
     * @param int $userId
     * @return array{subtotal: float, items_count: int}
     */
    public function getCartTotal(int $userId): array
    {
        $cartItems = $this->getCart($userId);

        $subtotal = 0.0;
        $itemsCount = 0;

        foreach ($cartItems as $item) {
            $price = $item->pricingTier
                ? (float) $item->pricingTier->price
                : (float) $item->foodItem->price;

            $subtotal += $price * $item->quantity;
            $itemsCount += $item->quantity;
        }

        return [
            'subtotal'    => round($subtotal, 2),
            'items_count' => $itemsCount,
        ];
    }

    /**
     * Validate cart items — separate available from unavailable.
     *
     * @param int $userId
     * @return array{valid: \Illuminate\Support\Collection, unavailable: \Illuminate\Support\Collection}
     */
    public function validateCart(int $userId): array
    {
        $cartItems = $this->getCart($userId);

        $valid = $cartItems->filter(function (FoodCart $item) {
            return $item->foodItem
                && $item->foodItem->is_available
                && $item->foodItem->status === 'active';
        });

        $unavailable = $cartItems->filter(function (FoodCart $item) {
            return !$item->foodItem
                || !$item->foodItem->is_available
                || $item->foodItem->status !== 'active';
        });

        return [
            'valid'       => $valid->values(),
            'unavailable' => $unavailable->values(),
        ];
    }
}
