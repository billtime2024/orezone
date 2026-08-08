<?php

namespace App\Events;

use App\Models\RentalReview;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalReviewCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RentalReview $review,
    ) {}
}
