<?php

namespace App\Services\Rental;

use App\Models\RentalAvailability;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    /**
     * Get calendar for a listing for a given month.
     */
    public function getCalendar(RentalListing $listing, string $month): array
    {
        $start = Carbon::parse($month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $period = CarbonPeriod::create($start, $end);
        $availability = $listing->availability()
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy('date');

        $calendar = [];
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $avail = $availability->get($dateStr);

            $calendar[$dateStr] = [
                'date' => $dateStr,
                'status' => $avail->status ?? 'available',
                'price' => $avail->price_override ?? $listing->price_per_unit,
                'is_weekend' => $date->isWeekend(),
            ];
        }

        return $calendar;
    }

    /**
     * Block specific dates for a listing.
     */
    public function blockDates(RentalListing $listing, array $dates, ?string $reason = null): void
    {
        foreach ($dates as $date) {
            RentalAvailability::updateOrCreate(
                ['rental_listing_id' => $listing->id, 'date' => $date],
                ['status' => 'blocked', 'reason' => $reason]
            );
        }
    }

    /**
     * Unblock specific dates for a listing.
     */
    public function unblockDates(RentalListing $listing, array $dates): void
    {
        RentalAvailability::where('rental_listing_id', $listing->id)
            ->whereIn('date', $dates)
            ->where('status', 'blocked')
            ->delete();
    }

    /**
     * Set peak/off-peak pricing for specific dates.
     */
    public function setPeakPricing(RentalListing $listing, array $dates, float $price): void
    {
        foreach ($dates as $date) {
            RentalAvailability::updateOrCreate(
                ['rental_listing_id' => $listing->id, 'date' => $date],
                ['status' => 'available', 'price_override' => $price]
            );
        }
    }

    /**
     * Get available room count for room-type listings.
     */
    public function getAvailableUnits(RentalListing $listing, string $checkIn, string $checkOut): int
    {
        if ($listing->rental_type !== 'room' || !$listing->roomDetails) {
            return 0;
        }

        $bookedRooms = $listing->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->count();

        return max(0, $listing->roomDetails->total_rooms - $bookedRooms);
    }

    /**
     * Get blocked dates for a listing.
     */
    public function getBlockedDates(RentalListing $listing, string $startDate, string $endDate): array
    {
        return $listing->availability()
            ->where('status', 'blocked')
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->toArray();
    }

    /**
     * Check if a specific date range is available.
     */
    public function isAvailable(RentalListing $listing, string $checkIn, string $checkOut): bool
    {
        return $listing->isAvailable($checkIn, $checkOut);
    }
}
