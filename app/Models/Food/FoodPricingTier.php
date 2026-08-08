<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodPricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_item_id',
        'tier_name',
        'quantity',
        'unit',
        'price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the food item this tier belongs to.
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    /**
     * Get the order items that use this pricing tier.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class, 'pricing_tier_id');
    }

    /**
     * Get the cart entries that use this pricing tier.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(FoodCart::class, 'pricing_tier_id');
    }
}
