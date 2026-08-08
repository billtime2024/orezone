<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'registration_number' => 'required|string|max:20',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:2030',
            'color' => 'sometimes|nullable|string|max:50',
            'seating_capacity' => 'required|integer|min:1|max:20',
        ];
    }
}
