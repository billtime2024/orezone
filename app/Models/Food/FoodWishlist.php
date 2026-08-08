<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodWishlist extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'food_item_id',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who owns this wishlist entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the food item in the wishlist.
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Toggle wishlist status for a user and item.
     */
    public static function toggle(int $userId, int $foodItemId): bool
    {
        $existing = static::where('user_id', $userId)
            ->where('food_item_id', $foodItemId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // Removed from wishlist
        }

        static::create([
            'user_id' => $userId,
            'food_item_id' => $foodItemId,
        ]);

        return true; // Added to wishlist
    }

    /**
     * Check if a user has wishlisted an item.
     */
    public static function isWishlisted(int $userId, int $foodItemId): bool
    {
        return static::where('user_id', $userId)
            ->where('food_item_id', $foodItemId)
            ->exists();
    }
}
