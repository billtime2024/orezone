<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_type',
        'business_name',
        'description',
        'logo_url',
        'cover_image_url',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'city',
        'state',
        'pincode',
        'fssai_license',
        'fssai_expiry',
        'gst_number',
        'pan_number',
        'verification_status',
        'verified_at',
        'is_active',
        'is_featured',
        'avg_rating',
        'total_orders',
        'total_revenue',
        'commission_rate',
        'operating_hours',
        'delivery_radius_km',
        'min_order_amount',
        'free_delivery_above',
        'bank_account_number',
        'bank_ifsc',
        'upi_id',
    ];

    protected $hidden = [
        'bank_account_number',
        'bank_ifsc',
        'upi_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'fssai_expiry' => 'date',
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'avg_rating' => 'decimal:2',
        'total_orders' => 'integer',
        'total_revenue' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'operating_hours' => 'array',
        'delivery_radius_km' => 'integer',
        'min_order_amount' => 'decimal:2',
        'free_delivery_above' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who owns this provider profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the food items offered by this provider.
     */
    public function items(): HasMany
    {
        return $this->hasMany(FoodItem::class, 'provider_id');
    }

    /**
     * Get the delivery slots offered by this provider.
     */
    public function deliverySlots(): HasMany
    {
        return $this->hasMany(FoodDeliverySlot::class, 'provider_id');
    }

    /**
     * Get the orders placed with this provider.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(FoodOrder::class, 'provider_id');
    }

    /**
     * Get the reviews for this provider.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(FoodReview::class, 'provider_id');
    }

    /**
     * Get the catering quotes sent by this provider.
     */
    public function cateringQuotes(): HasMany
    {
        return $this->hasMany(CateringQuote::class, 'provider_id');
    }

    /**
     * Get the hotel food services for this provider.
     */
    public function hotelServices(): HasMany
    {
        return $this->hasMany(HotelFoodService::class, 'provider_id');
    }

    /**
     * Get the promotions offered by this provider.
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(FoodPromotion::class, 'provider_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('provider_type', $type);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isVerified(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}
