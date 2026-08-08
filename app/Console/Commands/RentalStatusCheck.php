<?php

namespace App\Console\Commands;

use App\Services\Rental\BookingService;
use Illuminate\Console\Command;

class RentalStatusCheck extends Command
{
    protected $signature = 'rental:status-check';

    protected $description = 'Auto-transition rental bookings (check-in, check-out, expiry)';

    public function handle(BookingService $bookingService): int
    {
        $this->info('Running rental status check...');

        // Auto check-in: confirmed → active (check_in = today)
        $checkedIn = $bookingService->autoCheckIn();
        $this->info("  Check-in (confirmed → active): {$checkedIn} bookings");

        // Auto check-out: active → completed (check_out = today)
        $checkedOut = $bookingService->autoCheckOut();
        $this->info("  Check-out (active → completed): {$checkedOut} bookings");

        // Auto expire: pending → expired (older than 48h)
        $expired = $bookingService->autoExpire();
        $this->info("  Expired (pending → expired): {$expired} bookings");

        $total = $checkedIn + $checkedOut + $expired;
        $this->info("Done. Total transitions: {$total}");

        return Command::SUCCESS;
    }
}
