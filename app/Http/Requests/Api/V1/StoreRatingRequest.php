<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewee_id' => 'required|exists:users,id',
            'trip_id' => 'nullable|exists:trips,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reviewee_id.required' => 'The reviewee user is required.',
            'reviewee_id.exists' => 'The selected user does not exist.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'booking_id.exists' => 'The selected booking does not exist.',
            'rating.required' => 'A rating is required.',
            'rating.min' => 'The rating must be at least :min.',
            'rating.max' => 'The rating must not exceed :max.',
        ];
    }
}
