<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'departure_at' => 'required|date|after:now',
            'total_seats' => 'required|integer|min:1|max:20',
            'booking_mode' => 'sometimes|string|in:instant,request_approval',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists' => 'The selected vehicle does not exist.',
            'departure_at.after' => 'The departure time must be in the future.',
            'total_seats.min' => 'A trip must have at least 1 seat.',
            'booking_mode.in' => 'Booking mode must be either "instant" or "request_approval".',
        ];
    }
}
