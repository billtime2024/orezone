<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Trip extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ACTIVE = 'active';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ACTIVE,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // Booking mode constants
    const BOOKING_MODE_INSTANT = 'instant';
    const BOOKING_MODE_REQUEST = 'request';

    protected $fillable = [
        'host_id',
        'vehicle_id',
        'origin_name',
        'origin_address',
        'origin_lat',
        'origin_lng',
        'destination_name',
        'destination_address',
        'destination_lat',
        'destination_lng',
        'departure_at',
        'arrival_at',
        'total_seats',
        'available_seats',
        'booking_mode',
        'status',
        'route_polyline',
        'notes',
    ];

    protected $casts = [
        'origin_lat' => 'decimal:8',
        'origin_lng' => 'decimal:8',
        'destination_lat' => 'decimal:8',
        'destination_lng' => 'decimal:8',
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
        'total_seats' => 'integer',
        'available_seats' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the host who created the trip.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get the vehicle for this trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the stops for this trip.
     */
    public function stops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('sequence');
    }

    /**
     * Get the status history for this trip.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(TripStatusHistory::class);
    }

    /**
     * Get the bookings for this trip.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function confirmedBookings(): HasMany
    {
        return $this->bookings()->where('status', 'confirmed');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Check if the trip can be booked.
     */
    public function canBeBooked(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->available_seats > 0
            && $this->departure_at->isFuture();
    }

    /**
     * Check if the given user is the host of this trip.
     */
    public function isHost(User $user): bool
    {
        return $this->host_id === $user->id;
    }

    public function canBePublished(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PUBLISHED]);
    }

    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->available_seats === 0;
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Decrement available seats.
     */
    public function decrementSeats(int $count = 1): bool
    {
        if ($this->available_seats < $count) {
            return false;
        }

        return $this->decrement('available_seats', $count);
    }

    /**
     * Increment available seats.
     */
    public function incrementSeats(int $count = 1): bool
    {
        if ($this->available_seats + $count > $this->total_seats) {
            return false;
        }

        return $this->increment('available_seats', $count);
    }

    /**
     * Add a status change to the trip's history.
     */
    public function addStatusChange(string $status, ?int $changedBy = null, ?array $metadata = null): TripStatusHistory
    {
        return DB::transaction(function () use ($status, $changedBy, $metadata) {
            $this->update(['status' => $status]);

            return $this->statusHistory()->create([
                'status' => $status,
                'changed_by' => $changedBy,
                'metadata' => $metadata,
            ]);
        });
    }
}
