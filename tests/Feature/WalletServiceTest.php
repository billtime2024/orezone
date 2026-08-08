<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\RideSharing\WalletService;
use App\Services\RideSharing\TripService;
use App\Services\RideSharing\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WalletService();
        $this->user = User::factory()->create();
    }

    public function test_get_or_create_wallet(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $this->assertNotNull($wallet);
        $this->assertEquals('INR', $wallet->currency);
        $this->assertEquals(0, $wallet->balance);
        $this->assertTrue($wallet->is_active);
    }

    public function test_wallet_is_singleton(): void
    {
        $w1 = $this->service->getOrCreateWallet($this->user);
        $w2 = $this->service->getOrCreateWallet($this->user);
        $this->assertEquals($w1->id, $w2->id);
    }

    public function test_add_credit(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $tx = $this->service->addCredit($wallet, 500.00, 'admin_adjustment');
        $this->assertEquals(500, $tx->balance_after);
        $this->assertEquals('credit', $tx->direction);
        $wallet->refresh();
        $this->assertEquals(500, $wallet->balance);
    }

    public function test_add_refund_credit(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $tx = $this->service->addCredit($wallet, 250.50, 'refund');
        $this->assertEquals(250.50, $tx->balance_after);
        $wallet->refresh();
        $this->assertEquals(250.50, $wallet->balance);
    }

    public function test_add_promotional_credit(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $tx = $this->service->addCredit($wallet, 100, 'promotional_credit');
        $this->assertEquals(100, $tx->balance_after);
        $this->assertEquals('promotional_credit', $tx->type);
    }

    public function test_reject_invalid_credit_type(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credit type');
        $this->service->addCredit($wallet, 100, 'topup');
    }

    public function test_deduct_platform_fee(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $this->service->addCredit($wallet, 1000, 'admin_adjustment');

        // Create a trip + booking for fee deduction
        $host = User::factory()->create();
        $category = VehicleCategory::create(['name' => 'Sedan', 'slug' => 'sedan']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $host->id,
            'vehicle_category_id' => $category->id,
            'verification_status' => 'verified',
            'seating_capacity' => 4,
        ]);

        $tripService = new TripService();
        $trip = $tripService->createDraft($host, [
            'vehicle_id' => $vehicle->id,
            'origin_name' => 'Chennai',
            'destination_name' => 'Bangalore',
            'departure_at' => now()->addDays(2),
            'total_seats' => 3,
            'booking_mode' => 'instant',
        ]);
        $tripService->publishTrip($trip, $host);
        $trip->refresh(); // Must refresh after publish to pick up new status

        $bookingService = new BookingService();
        $booking = $bookingService->createBooking($trip, $this->user, [
            'seat_count' => 1,
        ]);
        // Set a platform fee on the booking
        $booking->update(['total_platform_fee' => 100]);
        $booking->refresh();

        $tx = $this->service->deductPlatformFee($wallet, $booking);
        $this->assertEquals(900, $tx->balance_after);
        $this->assertEquals('debit', $tx->direction);
        $this->assertEquals('platform_fee', $tx->type);
        $wallet->refresh();
        $this->assertEquals(900, $wallet->balance);
    }

    public function test_cannot_deduct_insufficient_balance(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);

        // Create a minimal booking for fee reference
        $host = User::factory()->create();
        $trip = Trip::factory()->create(['host_id' => $host->id]);
        $booking = Booking::factory()->create([
            'trip_id' => $trip->id,
            'traveler_id' => $this->user->id,
            'host_id' => $host->id,
            'total_platform_fee' => 500,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');
        $this->service->deductPlatformFee($wallet, $booking);
    }

    public function test_get_transactions(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $this->service->addCredit($wallet, 500, 'admin_adjustment');
        $this->service->addCredit($wallet, 200, 'refund');

        $transactions = $this->service->getTransactions($wallet);
        $this->assertEquals(2, $transactions->total());
    }

    public function test_get_transactions_filtered_by_type(): void
    {
        $wallet = $this->service->getOrCreateWallet($this->user);
        $this->service->addCredit($wallet, 500, 'admin_adjustment');
        $this->service->addCredit($wallet, 200, 'refund');

        $txns = $this->service->getTransactions($wallet, ['type' => 'refund']);
        $this->assertEquals(1, $txns->total());
        $this->assertEquals('refund', $txns->first()->type);
    }
}
