<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'seating_capacity' => $this->seating_capacity,
            'verification_status' => $this->verification_status,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'category' => new VehicleCategoryResource($this->whenLoaded('category')),
            'documents' => VehicleDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
