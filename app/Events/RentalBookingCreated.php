<?php

namespace App\Events;

use App\Models\RentalBooking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalBookingCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RentalBooking $booking,
    ) {}
}
