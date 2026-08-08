<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RentalListing;
use App\Services\Rental\ListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalListingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ListingService $listingService;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listingService = app(ListingService::class);
        $this->owner = User::factory()->create();
    }

    public function test_create_house_listing(): void
    {
        $data = [
            'user_id' => $this->owner->id,
            'rental_type' => 'house',
            'title' => 'Beach House in Goa',
            'price_per_unit' => 5000,
            'price_unit' => 'day',
            'address_line1' => '123 Beach Road',
            'city' => 'Goa',
            'state' => 'Goa',
            'pincode' => '403001',
        ];

        $details = [
            'bedrooms' => 3,
            'bathrooms' => 2,
            'max_guests' => 6,
            'area_sqft' => 1500,
        ];

        $listing = $this->listingService->createListing($data, $details);

        $this->assertNotNull($listing->id);
        $this->assertEquals('Beach House in Goa', $listing->title);
        $this->assertEquals('house', $listing->rental_type);
        $this->assertNotNull($listing->slug);
        $this->assertNotNull($listing->houseDetails);
        $this->assertEquals(3, $listing->houseDetails->bedrooms);
    }

    public function test_create_car_listing(): void
    {
        $data = [
            'user_id' => $this->owner->id,
            'rental_type' => 'car',
            'title' => 'Toyota Innova',
            'price_per_unit' => 1500,
            'price_unit' => 'day',
            'address_line1' => '456 MG Road',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
        ];

        $details = [
            'make' => 'Toyota',
            'model' => 'Innova',
            'year' => 2023,
            'fuel_type' => 'diesel',
            'transmission' => 'automatic',
            'seating_capacity' => 7,
        ];

        $listing = $this->listingService->createListing($data, $details);

        $this->assertEquals('car', $listing->rental_type);
        $this->assertNotNull($listing->carDetails);
        $this->assertEquals('Toyota', $listing->carDetails->make);
    }

    public function test_update_listing(): void
    {
        $listing = $this->createListing();

        $updated = $this->listingService->updateListing($listing, [
            'title' => 'Updated Title',
            'price_per_unit' => 6000,
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals(6000, $updated->price_per_unit);
    }

    public function test_toggle_status(): void
    {
        $listing = $this->createListing(['status' => 'active']);

        $toggled = $this->listingService->toggleStatus($listing);
        $this->assertEquals('paused', $toggled->status);

        $toggledAgain = $this->listingService->toggleStatus($toggled->fresh());
        $this->assertEquals('active', $toggledAgain->status);
    }

    public function test_delete_listing_without_bookings(): void
    {
        $listing = $this->createListing();

        $result = $this->listingService->deleteListing($listing);
        $this->assertTrue($result);
        $this->assertSoftDeleted('rental_listings', ['id' => $listing->id]);
    }

    public function test_search_listings(): void
    {
        $this->createListing(['city' => 'Goa', 'price_per_unit' => 3000]);
        $this->createListing(['city' => 'Chennai', 'price_per_unit' => 2000]);

        $results = $this->listingService->search(['city' => 'Goa']);
        $this->assertEquals(1, $results->total());

        $results = $this->listingService->search(['min_price' => 2500]);
        $this->assertEquals(1, $results->total());
    }

    private function createListing(array $overrides = []): RentalListing
    {
        $data = array_merge([
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
        ], $overrides);

        $details = match ($data['rental_type']) {
            'house' => ['bedrooms' => 2, 'bathrooms' => 1, 'max_guests' => 4],
            'car' => ['make' => 'Toyota', 'model' => 'Innova', 'year' => 2023, 'fuel_type' => 'diesel', 'transmission' => 'automatic', 'seating_capacity' => 7],
            default => ['bedrooms' => 1],
        };

        return $this->listingService->createListing($data, $details);
    }
}
