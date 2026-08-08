<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Rental\AvailabilityService;
use App\Services\Rental\ListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;
    private ListingService $listingService;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availabilityService = app(AvailabilityService::class);
        $this->listingService = app(ListingService::class);
        $this->owner = User::factory()->create();
    }

    public function test_get_calendar(): void
    {
        $listing = $this->createListing();
        $calendar = $this->availabilityService->getCalendar($listing, now()->format('Y-m'));

        $this->assertIsArray($calendar);
        $this->assertNotEmpty($calendar);

        // Each day should have status and price
        foreach ($calendar as $date => $day) {
            $this->assertArrayHasKey('status', $day);
            $this->assertArrayHasKey('price', $day);
            $this->assertEquals('available', $day['status']);
        }
    }

    public function test_block_dates(): void
    {
        $listing = $this->createListing();
        $dates = [
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
        ];

        $this->availabilityService->blockDates($listing, $dates, 'Owner vacation');

        $blockedCount = $listing->availability()
            ->whereIn('date', $dates)
            ->where('status', 'blocked')
            ->count();

        $this->assertEquals(2, $blockedCount);
    }

    public function test_unblock_dates(): void
    {
        $listing = $this->createListing();
        $dates = [now()->addDays(10)->format('Y-m-d')];

        $this->availabilityService->blockDates($listing, $dates);
        $this->availabilityService->unblockDates($listing, $dates);

        // Verify unblocked
        $blocked = $listing->availability()
            ->where('date', now()->addDays(10)->format('Y-m-d'))
            ->where('status', 'blocked')
            ->count();

        $this->assertEquals(0, $blocked);
    }

    public function test_set_peak_pricing(): void
    {
        $listing = $this->createListing();
        $dates = [now()->addDays(15)->format('Y-m-d')];

        $this->availabilityService->setPeakPricing($listing, $dates, 8000.0);

        $peakRecord = $listing->availability()
            ->where('date', now()->addDays(15)->format('Y-m-d'))
            ->first();

        $this->assertNotNull($peakRecord);
        $this->assertEquals(8000.0, $peakRecord->price_override);
    }

    private function createListing()
    {
        return $this->listingService->createListing([
            'user_id' => $this->owner->id,
            'rental_type' => 'house',
            'title' => 'Test Listing',
            'price_per_unit' => 5000,
            'price_unit' => 'day',
            'address_line1' => '123 Test Road',
            'city' => 'Goa',
            'state' => 'Goa',
            'pincode' => '403001',
            'status' => 'active',
        ], [
            'bedrooms' => 2,
            'bathrooms' => 1,
            'max_guests' => 4,
        ]);
    }
}
