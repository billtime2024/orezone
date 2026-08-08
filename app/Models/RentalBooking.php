<?php

namespace App\Models;

use App\Enums\RentalBookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBooking extends Model
{
    use SoftDeletes;

    protected $table = 'rental_bookings';

    protected $fillable = [
        'rental_listing_id', 'user_id', 'owner_id',
        'check_in', 'check_out', 'nights',
        'price_per_unit', 'subtotal', 'cleaning_fee',
        'security_deposit', 'service_fee', 'total_amount', 'currency',
        'status', 'payment_status', 'payment_method', 'payment_reference',
        'guest_message', 'host_message', 'booking_type',
        'cancellation_reason', 'cancelled_by', 'cancelled_at',
        'guests_count', 'special_requests', 'metadata',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'nights' => 'integer',
        'guests_count' => 'integer',
        'cancelled_at' => 'datetime',
        'special_requests' => 'array',
        'metadata' => 'array',
    ];

    // ── Relationships ──────────────────────────────

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RentalBookingStatusHistory::class, 'rental_booking_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(RentalReview::class, 'rental_booking_id');
    }

    // ── Helpers ────────────────────────────────────

    public function getStatusEnum(): RentalBookingStatus
    {
        return RentalBookingStatus::from($this->status);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function calculateTotal(): float
    {
        $this->subtotal = $this->price_per_unit * $this->nights;
        $this->service_fee = round($this->subtotal * 0.05, 2);
        $this->total_amount = $this->subtotal + $this->cleaning_fee + $this->service_fee;
        return $this->total_amount;
    }
}
