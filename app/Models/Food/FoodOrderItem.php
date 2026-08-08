<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_order_id',
        'food_item_id',
        'pricing_tier_id',
        'name',
        'price',
        'quantity',
        'total',
        'special_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    /**
     * Get the food item this order item represents.
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    /**
     * Get the pricing tier used for this order item.
     */
    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(FoodPricingTier::class, 'pricing_tier_id');
    }
}
