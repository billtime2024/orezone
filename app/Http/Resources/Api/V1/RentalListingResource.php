<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'rental_type' => $this->rental_type,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'price_per_unit' => $this->price_per_unit,
            'price_unit' => $this->price_unit,
            'formatted_price' => $this->formatted_price,
            'security_deposit' => $this->security_deposit,
            'cleaning_fee' => $this->cleaning_fee,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'instant_booking' => $this->instant_booking,
            'photos' => $this->photos ?? [],
            'rules' => $this->rules ?? [],
            'avg_rating' => $this->avg_rating,
            'review_count' => $this->review_count,
            'total_bookings' => $this->total_bookings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'house_details' => $this->whenLoaded('houseDetails'),
            'car_details' => $this->whenLoaded('carDetails'),
            'commercial_details' => $this->whenLoaded('commercialDetails'),
            'room_details' => $this->whenLoaded('roomDetails'),
            'details' => $this->whenLoaded('details'),
            'reviews_count' => $this->whenCounted('reviews'),
            'bookings_count' => $this->whenCounted('bookings'),
        ];
    }
}
