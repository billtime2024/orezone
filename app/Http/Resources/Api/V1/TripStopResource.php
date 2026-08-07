<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripStopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stop_order' => $this->stop_order,
            'name' => $this->name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'estimated_arrival' => $this->estimated_arrival?->toISOString(),
            'actual_arrival' => $this->actual_arrival?->toISOString(),
            'seats_taken' => $this->seats_taken,
        ];
    }
}
