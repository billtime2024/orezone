<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:10|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The top-up amount is required.',
            'amount.min' => 'The minimum top-up amount is :min.',
            'amount.max' => 'The maximum top-up amount is :max.',
        ];
    }
}
