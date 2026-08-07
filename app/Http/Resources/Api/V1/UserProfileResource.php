<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'bio'                     => $this->bio,
            'gender'                  => $this->gender,
            'date_of_birth'           => $this->date_of_birth,
            'address'                 => $this->address,
            'city'                    => $this->city,
            'country'                 => $this->country,
            'latitude'                => $this->latitude,
            'longitude'               => $this->longitude,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
        ];
    }
}
