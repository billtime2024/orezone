<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rental_listing_id' => $this->rental_listing_id,
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights' => $this->nights,
            'guests_count' => $this->guests_count,
            'guest_message' => $this->guest_message,
            'special_requests' => $this->special_requests,
            'price_per_unit' => $this->price_per_unit,
            'cleaning_fee' => $this->cleaning_fee,
            'security_deposit' => $this->security_deposit,
            'subtotal' => $this->subtotal,
            'service_fee' => $this->service_fee,
            'total_amount' => $this->total_amount,
            'booking_type' => $this->booking_type,
            'status' => $this->status,
            'host_message' => $this->host_message,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_by' => $this->cancelled_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'listing' => new RentalListingResource($this->whenLoaded('listing')),
            'guest' => new UserResource($this->whenLoaded('guest')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'status_history' => RentalBookingStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'review' => new RentalReviewResource($this->whenLoaded('review')),
        ];
    }
}
