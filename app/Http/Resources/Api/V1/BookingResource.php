<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seat_count' => $this->seat_count,
            'status' => $this->status,
            'idempotency_key' => $this->idempotency_key,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'trip' => new TripResource($this->whenLoaded('trip')),
            'traveler' => new UserResource($this->whenLoaded('traveler')),
            'pickup_stop' => new TripStopResource($this->whenLoaded('pickupStop')),
            'drop_stop' => new TripStopResource($this->whenLoaded('dropStop')),
        ];
    }
}
