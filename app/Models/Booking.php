<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_REQUESTED = 'requested';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_ACTIVE = 'active';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_REJECTED = 'rejected';

    const STATUS_NO_SHOW = 'no_show';

    const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_ACCEPTED,
        self::STATUS_CONFIRMED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_REJECTED,
        self::STATUS_NO_SHOW,
    ];

    protected $fillable = [
        'trip_id',
        'traveler_id',
        'host_id',
        'seat_count',
        'pickup_stop_id',
        'drop_stop_id',
        'status',
        'platform_fee',
        'platform_fee_tax',
        'total_platform_fee',
        'fee_snapshot',
        'idempotency_key',
        'requested_at',
        'accepted_at',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'seat_count' => 'integer',
        'platform_fee' => 'decimal:2',
        'platform_fee_tax' => 'decimal:2',
        'total_platform_fee' => 'decimal:2',
        'fee_snapshot' => 'array',
        'requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the trip for this booking.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the traveler (user) who made this booking.
     */
    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    /**
     * Get the host (user) who owns the trip.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get the pickup stop for this booking.
     */
    public function pickupStop(): BelongsTo
    {
        return $this->belongsTo(TripStop::class, 'pickup_stop_id');
    }

    /**
     * Get the drop-off stop for this booking.
     */
    public function dropStop(): BelongsTo
    {
        return $this->belongsTo(TripStop::class, 'drop_stop_id');
    }

    /**
     * Get the status history for this booking.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeRequested($query)
    {
        return $query->where('status', 'requested');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Add a status change to the booking's history.
     */
    public function addStatusChange(string $status, ?int $changedBy = null, ?array $metadata = null): BookingStatusHistory
    {
        return DB::transaction(function () use ($status, $changedBy, $metadata) {
            $this->update(['status' => $status]);

            // Set timestamp for the corresponding status
            $timestampField = $status.'_at';
            if (in_array($timestampField, ['requested_at', 'accepted_at', 'confirmed_at', 'cancelled_at', 'completed_at'])) {
                $this->update([$timestampField => now()]);
            }

            return $this->statusHistory()->create([
                'status' => $status,
                'changed_by' => $changedBy,
                'metadata' => $metadata,
            ]);
        });
    }

    public function canBeAccepted(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_REQUESTED, self::STATUS_CONFIRMED]);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_CONFIRMED && $this->trip->status === 'in_progress';
    }
}
