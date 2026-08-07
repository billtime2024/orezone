<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seat_count' => 'required|integer|min:1|max:10',
            'pickup_stop_id' => 'sometimes|nullable|exists:trip_stops,id',
            'drop_stop_id' => 'sometimes|nullable|exists:trip_stops,id',
            'idempotency_key' => 'sometimes|string|max:255|unique:bookings,idempotency_key',
            'notes' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_stop_id.exists' => 'The selected pickup stop does not exist.',
            'drop_stop_id.exists' => 'The selected drop-off stop does not exist.',
            'idempotency_key.unique' => 'This booking has already been submitted.',
        ];
    }
}
