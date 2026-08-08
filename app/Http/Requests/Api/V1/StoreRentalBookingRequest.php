<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');
        return $this->user()->can('create', [$listing]);
    }

    public function rules(): array
    {
        return [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'guest_message' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'check_in.after_or_equal' => 'Check-in date must be today or later.',
            'check_out.after' => 'Check-out date must be after check-in.',
            'guests_count.min' => 'At least 1 guest is required.',
        ];
    }
}
