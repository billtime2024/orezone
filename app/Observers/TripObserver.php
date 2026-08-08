<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Trip;

class TripObserver
{
    public function created(Trip $trip): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $trip->host_id,
            'auditable_type' => Trip::class,
            'auditable_id' => $trip->id,
            'action' => 'trip.created',
            'old_values' => [],
            'new_values' => $trip->toArray(),
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? '',
        ]);
    }

    public function updated(Trip $trip): void
    {
        $dirty = $trip->getDirty();

        if (isset($dirty['status'])) {
            AuditLog::create([
                'user_id' => auth()->id() ?? $trip->host_id,
                'auditable_type' => Trip::class,
                'auditable_id' => $trip->id,
                'action' => 'trip.status_changed',
                'old_values' => ['status' => $dirty['status'] ?? $trip->getOriginal('status')],
                'new_values' => ['status' => $trip->status],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? '',
            ]);
        }
    }
}
