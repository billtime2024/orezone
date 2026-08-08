<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireTrips extends Command
{
    protected $signature = 'trips:expire';
    protected $description = 'Expire trips that have passed their departure time and are still in published/full status';

    public function handle(): int
    {
        $expiredTrips = Trip::query()
            ->whereIn('status', [Trip::STATUS_PUBLISHED, Trip::STATUS_FULL])
            ->where('departure_at', '<', now())
            ->get();

        if ($expiredTrips->isEmpty()) {
            $this->info('No trips to expire.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredTrips->count()} trips to expire.");

        foreach ($expiredTrips as $trip) {
            DB::transaction(function () use ($trip) {
                // Update trip status to expired
                $trip->update(['status' => 'expired']);

                // Record status history
                $trip->statusHistory()->create([
                    'status' => 'expired',
                    'changed_by' => $trip->host_id,
                    'metadata' => ['reason' => 'Trip expired after departure time'],
                ]);

                // Cancel all active bookings
                $trip->bookings()
                    ->whereIn('status', [
                        Booking::STATUS_REQUESTED,
                        Booking::STATUS_ACCEPTED,
                        Booking::STATUS_CONFIRMED,
                    ])
                    ->each(function (Booking $booking) use ($trip) {
                        // Restore seats for confirmed bookings
                        if ($booking->status === Booking::STATUS_CONFIRMED) {
                            $trip->increment('available_seats', $booking->seat_count);
                        }

                        $booking->update([
                            'status' => Booking::STATUS_CANCELLED,
                            'cancelled_at' => now(),
                        ]);

                        BookingStatusHistory::create([
                            'booking_id' => $booking->id,
                            'status' => Booking::STATUS_CANCELLED,
                            'changed_by' => $trip->host_id,
                            'metadata' => ['reason' => 'Trip expired'],
                        ]);
                    });

                $this->line("  Trip #{$trip->id} ({$trip->origin_name} → {$trip->destination_name}) expired.");
            });
        }

        $this->info("Successfully expired {$expiredTrips->count()} trips.");
        return self::SUCCESS;
    }
}
