<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelFoodService extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'service_type',
        'name',
        'description',
        'is_24hr',
        'operating_start',
        'operating_end',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_24hr' => 'boolean',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the provider that owns this hotel service.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(FoodProvider::class, 'provider_id');
    }

    /**
     * Get the reservations for this hotel service.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(HotelReservation::class, 'hotel_service_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeRoomService($query)
    {
        return $query->where('service_type', 'room_service');
    }

    public function scopeRestaurant($query)
    {
        return $query->where('service_type', 'restaurant');
    }

    public function scopeBuffet($query)
    {
        return $query->where('service_type', 'buffet');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isOpenNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_24hr) {
            return true;
        }

        $now = now()->format('H:i:s');

        if ($this->operating_start && $now < $this->operating_start) {
            return false;
        }

        if ($this->operating_end && $now > $this->operating_end) {
            return false;
        }

        return true;
    }

    public function availableCapacity(): int
    {
        if (!$this->capacity) {
            return PHP_INT_MAX;
        }

        $todayReservations = $this->reservations()
            ->where('reservation_date', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->sum('party_size');

        return max(0, $this->capacity - $todayReservations);
    }
}
