<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rental_listing_id' => $this->rental_listing_id,
            'rental_booking_id' => $this->rental_booking_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'ratings' => $this->ratings,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'listing' => new RentalListingResource($this->whenLoaded('listing')),
            'booking' => new RentalBookingResource($this->whenLoaded('booking')),
        ];
    }
}
