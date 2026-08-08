<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalReview extends Model
{
    protected $table = 'rental_reviews';

    protected $fillable = [
        'rental_listing_id', 'rental_booking_id', 'user_id',
        'rating', 'comment', 'ratings', 'is_visible',
    ];

    protected $casts = [
        'ratings' => 'array',
        'is_visible' => 'boolean',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RentalBooking::class, 'rental_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
