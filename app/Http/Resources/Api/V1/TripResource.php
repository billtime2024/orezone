<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'origin_name' => $this->origin_name,
            'origin_lat' => $this->origin_lat,
            'origin_lng' => $this->origin_lng,
            'destination_name' => $this->destination_name,
            'destination_lat' => $this->destination_lat,
            'destination_lng' => $this->destination_lng,
            'departure_at' => $this->departure_at?->toISOString(),
            'arrival_at' => $this->arrival_at?->toISOString(),
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'booking_mode' => $this->booking_mode,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'host' => new UserResource($this->whenLoaded('host')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'stops' => TripStopResource::collection($this->whenLoaded('stops')),
            'bookings_count' => $this->whenCounted('bookings'),
        ];
    }
}
