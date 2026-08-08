<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodItemMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_item_id',
        'media_url',
        'media_type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the food item this media belongs to.
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
