<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Booking;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $booking->traveler_id,
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'action' => 'booking.created',
            'old_values' => [],
            'new_values' => $booking->toArray(),
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? '',
        ]);
    }

    public function updated(Booking $booking): void
    {
        $dirty = $booking->getDirty();

        if (isset($dirty['status'])) {
            AuditLog::create([
                'user_id' => auth()->id() ?? $booking->traveler_id,
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'action' => 'booking.status_changed',
                'old_values' => ['status' => $booking->getOriginal('status'), 'changed_by' => $booking->getOriginal('changed_by') ?? null],
                'new_values' => ['status' => $booking->status, 'changed_by' => $booking->changed_by ?? null],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? '',
            ]);
        }
    }
}
