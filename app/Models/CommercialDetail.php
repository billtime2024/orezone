<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialDetail extends Model
{
    protected $table = 'commercial_details';

    protected $fillable = [
        'rental_listing_id', 'property_type', 'area_sqft', 'carpet_area_sqft',
        'furnished', 'ac', 'power_backup', 'parking', 'parking_slots',
        'floor_number', 'total_floors', 'lift',
        'facilities', 'maintenance_charge', 'lease_type',
    ];

    protected $casts = [
        'area_sqft' => 'integer',
        'carpet_area_sqft' => 'integer',
        'parking_slots' => 'integer',
        'floor_number' => 'integer',
        'total_floors' => 'integer',
        'maintenance_charge' => 'decimal:2',
        'furnished' => 'boolean',
        'ac' => 'boolean',
        'power_backup' => 'boolean',
        'parking' => 'boolean',
        'lift' => 'boolean',
        'facilities' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }
}
