<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalAvailability extends Model
{
    protected $table = 'rental_availability';

    protected $fillable = [
        'rental_listing_id', 'date', 'status', 'price_override', 'reason',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }
}
