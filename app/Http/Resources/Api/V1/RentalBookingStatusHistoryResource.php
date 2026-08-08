<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalBookingStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rental_booking_id' => $this->rental_booking_id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'changed_by' => $this->changed_by,
            'actor_type' => $this->actor_type,
            'note' => $this->note,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'changed_by_user' => new UserResource($this->whenLoaded('changedByUser')),
        ];
    }
}
