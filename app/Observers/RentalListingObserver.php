<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\RentalListing;

class RentalListingObserver
{
    public function created(RentalListing $listing): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $listing->user_id,
            'auditable_type' => RentalListing::class,
            'auditable_id' => $listing->id,
            'action' => 'rental_listing.created',
            'old_values' => [],
            'new_values' => $listing->toArray(),
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? '',
        ]);
    }

    public function updated(RentalListing $listing): void
    {
        $dirty = $listing->getDirty();

        if (isset($dirty['status'])) {
            AuditLog::create([
                'user_id' => auth()->id() ?? $listing->user_id,
                'auditable_type' => RentalListing::class,
                'auditable_id' => $listing->id,
                'action' => 'rental_listing.status_changed',
                'old_values' => ['status' => $listing->getOriginal('status')],
                'new_values' => ['status' => $listing->status],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? '',
            ]);
        }
    }
}
