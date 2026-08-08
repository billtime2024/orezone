<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalListing extends Model
{
    use SoftDeletes;

    protected $table = 'rental_listings';

    protected $fillable = [
        'user_id', 'rental_type', 'title', 'description', 'slug',
        'price_per_unit', 'price_unit', 'security_deposit', 'cleaning_fee',
        'address_line1', 'address_line2', 'city', 'state', 'pincode',
        'latitude', 'longitude', 'status', 'instant_booking',
        'blocked_dates', 'photos', 'rules',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'blocked_dates' => 'array',
        'photos' => 'array',
        'rules' => 'array',
    ];

    // ── Relationships ──────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class, 'rental_listing_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(RentalReview::class, 'rental_listing_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(RentalAvailability::class, 'rental_listing_id');
    }

    // ── Type-specific relations ────────────────────

    public function houseDetails(): HasOne
    {
        return $this->hasOne(HouseDetail::class, 'rental_listing_id');
    }

    public function carDetails(): HasOne
    {
        return $this->hasOne(CarDetail::class, 'rental_listing_id');
    }

    public function commercialDetails(): HasOne
    {
        return $this->hasOne(CommercialDetail::class, 'rental_listing_id');
    }

    public function roomDetails(): HasOne
    {
        return $this->hasOne(RoomDetail::class, 'rental_listing_id');
    }

    // ── Accessors ──────────────────────────────────

    protected function details(): Attribute
    {
        return Attribute::get(fn () => match ($this->rental_type) {
            'house' => $this->houseDetails,
            'car' => $this->carDetails,
            'commercial' => $this->commercialDetails,
            'room' => $this->roomDetails,
        });
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => '₹' . number_format($this->price_per_unit, 0) . ' / ' . $this->price_unit);
    }

    // ── Availability check ─────────────────────────

    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        $range = collect(range(strtotime($checkIn), strtotime($checkOut), 86400))
            ->map(fn ($ts) => date('Y-m-d', $ts));

        // Check JSON blocked_dates column
        $blocked = $this->blocked_dates ?? [];
        if ($range->intersect($blocked)->isNotEmpty()) {
            return false;
        }

        // Check rental_availability table for blocked dates
        $blockedFromTable = $this->availability()
            ->where('status', 'blocked')
            ->whereIn('date', $range->toArray())
            ->exists();

        if ($blockedFromTable) {
            return false;
        }

        $overlap = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->exists();

        return !$overlap;
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('rental_type', $type);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price_per_unit', [$min, $max]);
    }
}
