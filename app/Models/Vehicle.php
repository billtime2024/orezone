<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_category_id',
        'registration_number',
        'brand',
        'model',
        'year',
        'color',
        'seating_capacity',
        'verification_status',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'seating_capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who owns the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle's category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    /**
     * Get the vehicle's documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    /**
     * Get the verification requests for this vehicle.
     */
    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }
}
