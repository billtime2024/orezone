<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'max_uses',
        'used_count',
        'applicable_to',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the provider this promotion belongs to (null = platform-wide).
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }

    public function scopePlatformWide($query)
    {
        return $query->whereNull('provider_id');
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopePercentage($query)
    {
        return $query->where('discount_type', 'percentage');
    }

    public function scopeFixed($query)
    {
        return $query->where('discount_type', 'fixed');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $orderAmount * ($this->discount_value / 100);

            if ($this->max_discount !== null) {
                $discount = min($discount, $this->max_discount);
            }

            return round($discount, 2);
        }

        // Fixed discount
        return min($this->discount_value, $orderAmount);
    }

    public function isPlatformWide(): bool
    {
        return $this->provider_id === null;
    }

    public function hasUsageLimit(): bool
    {
        return $this->max_uses !== null;
    }

    public function remainingUses(): ?int
    {
        if ($this->max_uses === null) {
            return null;
        }

        return max(0, $this->max_uses - $this->used_count);
    }
}
