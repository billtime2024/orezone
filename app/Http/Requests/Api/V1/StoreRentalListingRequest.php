<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rental_type' => 'required|in:house,car,commercial,room',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price_per_unit' => 'required|numeric|min:1',
            'price_unit' => 'required|in:hour,day,month,year',
            'security_deposit' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'instant_booking' => 'nullable|boolean',
            'blocked_dates' => 'nullable|array',
            'blocked_dates.*' => 'date',
            'rules' => 'nullable|array',
            'rules.*' => 'string|max:255',
            'details' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'rental_type.in' => 'Rental type must be house, car, commercial, or room.',
            'price_per_unit.min' => 'Price must be at least ₹1.',
            'price_unit.in' => 'Price unit must be hour, day, month, or year.',
        ];
    }
}
