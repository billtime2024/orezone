<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'reported_user' => new UserResource($this->whenLoaded('reportedUser')),
            'trip' => new TripResource($this->whenLoaded('trip')),
            'booking' => new BookingResource($this->whenLoaded('booking')),
        ];
    }
}
