<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringQuote extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'catering_request_id',
        'provider_id',
        'quoted_amount',
        'proposed_menu',
        'includes_decor',
        'includes_service_staff',
        'staff_count',
        'notes',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'proposed_menu' => 'array',
        'includes_decor' => 'boolean',
        'includes_service_staff' => 'boolean',
        'staff_count' => 'integer',
        'valid_until' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the catering request this quote is for.
     */
    public function cateringRequest(): BelongsTo
    {
        return $this->belongsTo(CateringRequest::class, 'catering_request_id');
    }

    /**
     * Get the provider that sent this quote.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }

    public function scopeValid($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('valid_until', '>=', now());
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->valid_until->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
}
