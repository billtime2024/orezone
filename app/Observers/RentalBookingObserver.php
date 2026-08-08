<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\RentalBooking;

class RentalBookingObserver
{
    public function created(RentalBooking $booking): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $booking->user_id,
            'auditable_type' => RentalBooking::class,
            'auditable_id' => $booking->id,
            'action' => 'rental_booking.created',
            'old_values' => [],
            'new_values' => $booking->toArray(),
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? '',
        ]);
    }

    public function updated(RentalBooking $booking): void
    {
        $dirty = $booking->getDirty();

        if (isset($dirty['status'])) {
            AuditLog::create([
                'user_id' => auth()->id() ?? $booking->user_id,
                'auditable_type' => RentalBooking::class,
                'auditable_id' => $booking->id,
                'action' => 'rental_booking.status_changed',
                'old_values' => ['status' => $booking->getOriginal('status')],
                'new_values' => ['status' => $booking->status],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? '',
            ]);
        }
    }
}
