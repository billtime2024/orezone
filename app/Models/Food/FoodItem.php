<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'slug',
        'description',
        'image_url',
        'price',
        'discount_price',
        'unit',
        'min_quantity',
        'max_quantity',
        'preparation_time_min',
        'is_jain',
        'is_vegan',
        'spice_level',
        'allergens',
        'ingredients',
        'is_available',
        'is_featured',
        'status',
        'price_range',
        'available_days',
        'available_from',
        'available_to',
        'total_orders',
        'avg_rating',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'preparation_time_min' => 'integer',
        'is_jain' => 'boolean',
        'is_vegan' => 'boolean',
        'allergens' => 'array',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'available_days' => 'array',
        'total_orders' => 'integer',
        'avg_rating' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the provider that offers this item.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the category this item belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }

    /**
     * Get the media (photos/videos) for this item.
     */
    public function media(): HasMany
    {
        return $this->hasMany(FoodItemMedia::class, 'food_item_id');
    }

    /**
     * Get the pricing tiers for this item.
     */
    public function pricingTiers(): HasMany
    {
        return $this->hasMany(FoodPricingTier::class, 'food_item_id');
    }

    /**
     * Get the order items that include this item.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class, 'food_item_id');
    }

    /**
     * Get the reviews for this item.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(FoodReview::class, 'food_item_id');
    }

    /**
     * Get the cart entries for this item.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(FoodCart::class, 'food_item_id');
    }

    /**
     * Get the wishlist entries for this item.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(FoodWishlist::class, 'food_item_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeJain($query)
    {
        return $query->where('is_jain', true);
    }

    public function scopeVegan($query)
    {
        return $query->where('is_vegan', true);
    }

    public function scopeBySpiceLevel($query, string $level)
    {
        return $query->where('spice_level', $level);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isAvailableToday(): bool
    {
        $today = strtolower(now()->format('D')); // mon, tue, ...

        if (!in_array($today, $this->available_days ?? [])) {
            return false;
        }

        $now = now()->format('H:i:s');

        if ($this->available_from && $now < $this->available_from) {
            return false;
        }

        if ($this->available_to && $now > $this->available_to) {
            return false;
        }

        return true;
    }

    public function effectivePrice(): string
    {
        return $this->discount_price ?? $this->price;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }
}
