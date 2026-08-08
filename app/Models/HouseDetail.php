<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseDetail extends Model
{
    protected $table = 'house_details';

    protected $fillable = [
        'rental_listing_id', 'bedrooms', 'bathrooms', 'floors',
        'furnished', 'parking', 'ac', 'wifi',
        'amenities', 'property_type', 'area_sqft',
    ];

    protected $casts = [
        'amenities' => 'array',
        'furnished' => 'boolean',
        'parking' => 'boolean',
        'ac' => 'boolean',
        'wifi' => 'boolean',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }
}
