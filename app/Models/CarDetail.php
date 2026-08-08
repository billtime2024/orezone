<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarDetail extends Model
{
    protected $table = 'car_details';

    protected $fillable = [
        'rental_listing_id', 'make', 'model', 'year', 'color',
        'fuel_type', 'transmission', 'seats',
        'self_drive', 'with_driver', 'driver_charge_per_day',
        'mileage_km', 'registration_number',
        'insurance_details', 'documents',
    ];

    protected $casts = [
        'year' => 'integer',
        'seats' => 'integer',
        'mileage_km' => 'integer',
        'driver_charge_per_day' => 'decimal:2',
        'self_drive' => 'boolean',
        'with_driver' => 'boolean',
        'insurance_details' => 'array',
        'documents' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }
}
