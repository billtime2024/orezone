<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_category_id' => 'sometimes|nullable|exists:vehicle_categories,id',
            'registration_number' => 'sometimes|string|max:20',
            'brand' => 'sometimes|nullable|string|max:100',
            'model' => 'sometimes|nullable|string|max:100',
            'year' => 'sometimes|nullable|integer|min:1900|max:2030',
            'color' => 'sometimes|nullable|string|max:50',
            'seating_capacity' => 'sometimes|nullable|integer|min:1|max:20',
        ];
    }
}
