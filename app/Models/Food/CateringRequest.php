<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CateringRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_QUOTES_RECEIVED = 'quotes_received';
    const STATUS_QUOTE_SELECTED = 'quote_selected';
    const STATUS_TASTING_SCHEDULED = 'tasting_scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_QUOTES_RECEIVED,
        self::STATUS_QUOTE_SELECTED,
        self::STATUS_TASTING_SCHEDULED,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'request_number',
        'user_id',
        'provider_id',
        'event_type',
        'event_name',
        'event_date',
        'event_end_date',
        'event_time',
        'venue_address',
        'venue_latitude',
        'venue_longitude',
        'guest_count',
        'budget_min',
        'budget_max',
        'cuisine_preferences',
        'dietary_requirements',
        'menu_description',
        'special_requests',
        'tasting_requested',
        'tasting_date',
        'status',
        'total_amount',
        'advance_paid',
        'payment_status',
        'cancellation_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_end_date' => 'date',
        'venue_latitude' => 'decimal:7',
        'venue_longitude' => 'decimal:7',
        'guest_count' => 'integer',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'cuisine_preferences' => 'array',
        'dietary_requirements' => 'array',
        'tasting_requested' => 'boolean',
        'tasting_date' => 'date',
        'total_amount' => 'decimal:2',
        'advance_paid' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who made this catering request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned provider.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the quotes received for this request.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(CateringQuote::class, 'catering_request_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_QUOTES_RECEIVED,
            self::STATUS_QUOTE_SELECTED,
            self::STATUS_TASTING_SCHEDULED,
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isMultiDay(): bool
    {
        return $this->event_end_date !== null
            && $this->event_end_date->gt($this->event_date);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_QUOTES_RECEIVED,
            self::STATUS_QUOTE_SELECTED,
        ]);
    }

    public function totalBudget(): ?float
    {
        if ($this->budget_min && $this->budget_max) {
            return ($this->budget_min + $this->budget_max) / 2;
        }

        return $this->budget_max ?? $this->budget_min;
    }
}
