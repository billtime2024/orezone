<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodDeliverySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'day_of_week',
        'slot_start',
        'slot_end',
        'max_orders',
        'current_orders',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'max_orders' => 'integer',
        'current_orders' => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the provider that owns this slot.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the orders using this delivery slot.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(FoodOrder::class, 'delivery_slot_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->whereRaw('current_orders < max_orders');
    }

    public function scopeForDay($query, int $day)
    {
        return $query->where('day_of_week', $day);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->is_active && $this->current_orders < $this->max_orders;
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->max_orders - $this->current_orders);
    }
}
