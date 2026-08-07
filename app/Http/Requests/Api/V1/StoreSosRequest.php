<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id' => 'nullable|exists:trips,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'message' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'trip_id.exists' => 'The selected trip does not exist.',
            'latitude.required' => 'Your current latitude is required.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
            'longitude.required' => 'Your current longitude is required.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
        ];
    }
}
