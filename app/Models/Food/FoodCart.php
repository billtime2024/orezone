<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodCart extends Model
{
    use HasFactory;
    protected $table = 'food_cart';

    protected $fillable = [
        'user_id',
        'food_item_id',
        'pricing_tier_id',
        'quantity',
        'special_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who owns this cart entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the food item in the cart.
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    /**
     * Get the pricing tier selected for this cart entry.
     */
    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(FoodPricingTier::class, 'pricing_tier_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForItem($query, int $itemId)
    {
        return $query->where('food_item_id', $itemId);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function lineTotal(): float
    {
        $price = $this->pricingTier
            ? $this->pricingTier->price
            : $this->foodItem->effectivePrice();

        return round((float) $price * $this->quantity, 2);
    }
}
