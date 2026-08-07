<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name'                    => 'sometimes|string|max:255',
            'bio'                     => 'sometimes|nullable|string|max:1000',
            'gender'                  => 'sometimes|nullable|in:male,female,other,prefer_not_to_say',
            'date_of_birth'           => 'sometimes|nullable|date|before:today',
            'address'                 => 'sometimes|nullable|string|max:500',
            'city'                    => 'sometimes|nullable|string|max:100',
            'country'                 => 'sometimes|nullable|string|max:2',
            'latitude'                => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'               => 'sometimes|nullable|numeric|between:-180,180',
            'emergency_contact_name'  => 'sometimes|nullable|string|max:255',
            'emergency_contact_phone' => 'sometimes|nullable|string|max:15',
        ];
    }
}
