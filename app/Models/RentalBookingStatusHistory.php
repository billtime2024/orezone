<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBookingStatusHistory extends Model
{
    protected $table = 'rental_booking_status_histories';

    protected $fillable = [
        'rental_booking_id', 'from_status', 'to_status',
        'changed_by', 'actor_type', 'note',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RentalBooking::class, 'rental_booking_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
