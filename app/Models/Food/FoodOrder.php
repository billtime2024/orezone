<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodOrder extends Model
{
    use HasFactory;

    const STATUS_PLACED = 'placed';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const STATUSES = [
        self::STATUS_PLACED,
        self::STATUS_CONFIRMED,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number',
        'user_id',
        'provider_id',
        'order_type',
        'status',
        'delivery_type',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_slot_id',
        'scheduled_at',
        'subtotal',
        'delivery_charge',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'commission_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'special_instructions',
        'cancellation_reason',
        'cancelled_at',
        'refunded_at',
        'refund_amount',
        'refund_status',
        'refund_reason',
        'payout_status',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
        'scheduled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'delivered_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider fulfilling this order.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class, 'food_order_id');
    }

    /**
     * Get the delivery slot for this order.
     */
    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(FoodDeliverySlot::class, 'delivery_slot_id');
    }

    /**
     * Get the reviews for this order.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(FoodReview::class, 'food_order_id');
    }

    /**
     * Get the status change history for this order.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(FoodOrderStatusHistory::class, 'food_order_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PLACED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PLACED,
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
        ]);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isDelivery(): bool
    {
        return $this->delivery_type === 'delivery';
    }

    public function isPickup(): bool
    {
        return $this->delivery_type === 'pickup';
    }
}
