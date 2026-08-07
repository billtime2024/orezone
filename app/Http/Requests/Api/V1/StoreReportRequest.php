<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_user_id' => 'required|exists:users,id',
            'trip_id' => 'nullable|exists:trips,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'reported_user_id.required' => 'The user to report is required.',
            'reported_user_id.exists' => 'The selected user does not exist.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'booking_id.exists' => 'The selected booking does not exist.',
            'reason.required' => 'A reason for the report is required.',
        ];
    }
}
