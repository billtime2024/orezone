<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'food_item_id',
        'food_order_id',
        'rating',
        'taste_rating',
        'packaging_rating',
        'delivery_rating',
        'comment',
        'reply',
        'replied_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'taste_rating' => 'integer',
        'packaging_rating' => 'integer',
        'delivery_rating' => 'integer',
        'replied_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who wrote this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider being reviewed.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the food item being reviewed (null = provider review).
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    /**
     * Get the order this review is for.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeProviderReviews($query)
    {
        return $query->whereNull('food_item_id');
    }

    public function scopeItemReviews($query)
    {
        return $query->whereNotNull('food_item_id');
    }

    public function scopeHighRated($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeWithReply($query)
    {
        return $query->whereNotNull('reply');
    }

    public function scopeWithoutReply($query)
    {
        return $query->whereNull('reply');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isProviderReview(): bool
    {
        return $this->food_item_id === null;
    }

    public function isItemReview(): bool
    {
        return $this->food_item_id !== null;
    }

    public function hasReply(): bool
    {
        return $this->reply !== null;
    }

    public function averageSubRating(): ?float
    {
        $ratings = array_filter([
            $this->taste_rating,
            $this->packaging_rating,
            $this->delivery_rating,
        ]);

        if (empty($ratings)) {
            return null;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }
}
