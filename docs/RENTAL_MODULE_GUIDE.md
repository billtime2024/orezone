# Rental Module Architecture Guide — Orezone
## House Rent | Car Rent | Commercial Building Rent | Room Stay

**Project:** Laravel 12 + Inertia + Vue3 + Sanctum + Flutter
**Architecture:** Monolithic (no nwidart modules — standard Laravel structure)

---

## 1. CORE ARCHITECTURE DECISION: Polymorphic Rental System

All 4 rental types share 80% of the same logic. Instead of 4 separate modules,
build ONE unified "Rentals" module with a **type-driven polymorphic design**.

```
                    ┌─────────────────────┐
                    │   Rental Listing     │  (base table)
                    │   rental_type enum:  │
                    │   house|car|commercial|room
                    ├─────────────────────┤
                    │   id                │
                    │   user_id (owner)   │
                    │   rental_type       │
                    │   title             │
                    │   description       │
                    │   price_per_unit    │
                    │   price_unit (day/month/year/hour)
                    │   deposit_amount    │
                    │   status (draft/active/paused/closed)
                    │   latitude/longitude│
                    │   city/state/pin    │
                    │   photos (JSON)     │
                    │   rules (JSON)      │
                    └────────┬────────────┘
                             │
            ┌────────────────┼────────────────┐
            ▼                ▼                 ▼
   ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
   │ house_details│ │ car_details  │ │ room_details │
   │ (1:1 morph)  │ │ (1:1 morph)  │ │ (1:1 morph)  │
   └──────────────┘ └──────────────┘ └──────────────┘
                                    ┌──────────────┐
                                    │commercial_det│
                                    │ails (1:1)    │
                                    └──────────────┘
```

**WHY polymorphic instead of 4 separate modules:**
- 80% shared code (CRUD, search, booking, payment, reviews)
- One search endpoint filters all types
- Unified dashboard for owners who list multiple types
- Simpler admin panel — one sidebar entry with sub-filters
- Easier to add new types later (e.g., equipment, parking)

---

## 2. DATABASE SCHEMA

### 2a. Base Table: `rental_listings`

```php
Schema::create('rental_listings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('rental_type', ['house', 'car', 'commercial', 'room']);
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('slug')->unique();

    // Pricing
    $table->decimal('price_per_unit', 12, 2);
    $table->enum('price_unit', ['hour', 'day', 'month', 'year'])->default('month');
    $table->decimal('security_deposit', 12, 2)->default(0);
    $table->decimal('cleaning_fee', 12, 2)->default(0);

    // Location
    $table->string('address_line1');
    $table->string('address_line2')->nullable();
    $table->string('city');
    $table->string('state');
    $table->string('pincode', 10);
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();

    // Availability
    $table->enum('status', ['draft', 'active', 'paused', 'closed'])->default('draft');
    $table->boolean('instant_booking')->default(false);
    $table->json('blocked_dates')->nullable(); // ["2026-08-15", "2026-08-16"]

    // Media
    $table->json('photos')->nullable(); // ["url1", "url2"]
    $table->json('rules')->nullable(); // ["no_smoking", "no_pets"]

    // Counts (denormalized for search performance)
    $table->unsignedInteger('total_bookings')->default(0);
    $table->decimal('avg_rating', 3, 2)->default(0);
    $table->unsignedInteger('review_count')->default(0);

    $table->timestamps();
    $table->softDeletes();

    $table->index(['rental_type', 'status']);
    $table->index(['city', 'status']);
    $table->index(['user_id', 'rental_type']);
});
```

### 2b. Type-Specific Detail Tables (1:1 polymorphic)

```php
// House-specific details
Schema::create('house_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('bedrooms')->default(1);
    $table->unsignedTinyInteger('bathrooms')->default(1);
    $table->unsignedTinyInteger('floors')->default(1);
    $table->boolean('furnished')->default(false);
    $table->boolean('parking')->default(false);
    $table->boolean('ac')->default(false);
    $table->boolean('wifi')->default(false);
    $table->json('amenities')->nullable(); // ["washing_machine", "gym", "pool"]
    $table->enum('property_type', ['apartment', 'independent_house', 'villa', 'pg', 'hostel']);
    $table->unsignedInteger('area_sqft')->nullable();
    $table->timestamps();
});

// Car-specific details
Schema::create('car_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->string('make'); // Toyota, Hyundai
    $table->string('model'); // Innova, Creta
    $table->unsignedSmallInteger('year');
    $table->string('color')->nullable();
    $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
    $table->enum('transmission', ['manual', 'automatic']);
    $table->unsignedTinyInteger('seats')->default(5);
    $table->boolean('self_drive')->default(true);
    $table->boolean('with_driver')->default(false);
    $table->decimal('driver_charge_per_day', 10, 2)->default(0);
    $table->unsignedInteger('mileage_km')->nullable();
    $table->string('registration_number')->nullable();
    $table->json('insurance_details')->nullable();
    $table->json('documents')->nullable(); // RC, insurance, PUC
    $table->timestamps();
});

// Commercial building details
Schema::create('commercial_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->enum('property_type', ['office', 'shop', 'warehouse', 'godown', 'showroom', 'co_working']);
    $table->unsignedInteger('area_sqft');
    $table->unsignedInteger('carpet_area_sqft')->nullable();
    $table->boolean('furnished')->default(false);
    $table->boolean('ac')->default(false);
    $table->boolean('power_backup')->default(false);
    $table->boolean('parking')->default(false);
    $table->unsignedInteger('parking_slots')->default(0);
    $table->unsignedInteger('floor_number')->nullable();
    $table->unsignedInteger('total_floors')->nullable();
    $table->boolean('lift')->default(false);
    $table->json('facilities')->nullable(); // ["cctv", "security", "conference_room"]
    $table->decimal('maintenance_charge', 10, 2)->default(0);
    $table->enum('lease_type', ['bare_shell', 'fitted', 'semi_furnished', 'fully_furnished']);
    $table->timestamps();
});

// Room stay details (PG, hotel-like)
Schema::create('room_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->enum('room_type', ['single', 'double', 'triple', 'dorm', 'suite']);
    $table->enum('stay_type', ['pg', 'hostel', 'hotel', 'homestay', 'co_living']);
    $table->boolean('meals_included')->default(false);
    $table->enum('meal_plan', ['none', 'breakfast', 'half_board', 'full_board'])->default('none');
    $table->boolean('ac')->default(false);
    $table->boolean('wifi')->default(false);
    $table->boolean('laundry')->default(false);
    $table->boolean('housekeeping')->default(false);
    $table->boolean('curfew_time')->default(false);
    $table->time('check_in_time')->default('12:00');
    $table->time('check_out_time')->default('11:00');
    $table->json('rules')->nullable(); // ["no_visitors_after_10pm", "id_required"]
    $table->json('common_areas')->nullable(); // ["kitchen", "lounge", "terrace"]
    $table->unsignedInteger('total_rooms')->default(1);
    $table->unsignedInteger('available_rooms')->default(1);
    $table->timestamps();
});
```

### 2c. Bookings Table (shared for all rental types)

```php
Schema::create('rental_bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // guest/booker
    $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

    // Dates
    $table->date('check_in');
    $table->date('check_out');
    $table->unsignedInteger('nights'); // or hours for hourly rentals

    // Pricing (snapshot at booking time)
    $table->decimal('price_per_unit', 12, 2);
    $table->decimal('subtotal', 12, 2);
    $table->decimal('cleaning_fee', 12, 2)->default(0);
    $table->decimal('security_deposit', 12, 2)->default(0);
    $table->decimal('service_fee', 12, 2)->default(0); // platform commission
    $table->decimal('total_amount', 12, 2);
    $table->string('currency', 3)->default('INR');

    // Status
    $table->enum('status', [
        'pending', 'confirmed', 'active', 'completed',
        'cancelled_by_guest', 'cancelled_by_host', 'rejected', 'expired', 'disputed'
    ])->default('pending');

    // Payment
    $table->enum('payment_status', [
        'pending', 'authorized', 'captured', 'partial_refund',
        'full_refund', 'refunded', 'failed'
    ])->default('pending');
    $table->string('payment_method')->nullable();
    $table->string('payment_reference')->nullable();

    // Communication
    $table->text('guest_message')->nullable();
    $table->text('host_message')->nullable(); // host response to booking request
    $table->enum('booking_type', ['instant', 'request'])->default('instant');

    // Cancellation
    $table->text('cancellation_reason')->nullable();
    $table->string('cancelled_by')->nullable();
    $table->timestamp('cancelled_at')->nullable();

    // Metadata
    $table->unsignedInteger('guests_count')->default(1);
    $table->json('special_requests')->nullable();
    $table->json('metadata')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['rental_listing_id', 'status']);
    $table->index(['user_id', 'status']); // my bookings
    $table->index(['owner_id', 'status']); // owner's bookings
    $table->index(['check_in', 'check_out']); // availability check
});
```

### 2d. Availability Calendar (for complex availability management)

```php
Schema::create('rental_availability', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->enum('status', ['available', 'blocked', 'booked'])->default('available');
    $table->decimal('price_override', 12, 2)->nullable(); // peak season pricing
    $table->string('reason')->nullable(); // "maintenance", "owner_use"
    $table->timestamps();

    $table->unique(['rental_listing_id', 'date']);
});
```

### 2e. Reviews (shared across all types)

```php
Schema::create('rental_reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
    $table->foreignId('rental_booking_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // reviewer
    $table->unsignedTinyInteger('rating'); // 1-5
    $table->text('comment')->nullable();
    $table->json('ratings')->nullable(); // {"cleanliness":5,"location":4,"value":5}
    $table->boolean('is_visible')->default(true);
    $table->timestamps();

    $table->unique(['rental_listing_id', 'rental_booking_id']); // one review per booking
});
```

---

## 3. ELOQUENT MODELS

### 3a. RentalListing (base model)

```php
// app/Models/RentalListing.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalListing extends Model
{
    use SoftDeletes;

    protected $table = 'rental_listings';

    protected $fillable = [
        'user_id', 'rental_type', 'title', 'description', 'slug',
        'price_per_unit', 'price_unit', 'security_deposit', 'cleaning_fee',
        'address_line1', 'address_line2', 'city', 'state', 'pincode',
        'latitude', 'longitude', 'status', 'instant_booking',
        'blocked_dates', 'photos', 'rules',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'blocked_dates' => 'array',
        'photos' => 'array',
        'rules' => 'array',
    ];

    // ── Relationships ──────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class, 'rental_listing_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(RentalReview::class, 'rental_listing_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(RentalAvailability::class, 'rental_listing_id');
    }

    // ── Type-specific relations (1:1 polymorphic) ──

    public function houseDetails(): HasOne
    {
        return $this->hasOne(HouseDetail::class, 'rental_listing_id');
    }

    public function carDetails(): HasOne
    {
        return $this->hasOne(CarDetail::class, 'rental_listing_id');
    }

    public function commercialDetails(): HasOne
    {
        return $this->hasOne(CommercialDetail::class, 'rental_listing_id');
    }

    public function roomDetails(): HasOne
    {
        return $this->hasOne(RoomDetail::class, 'rental_listing_id');
    }

    // ── Type accessor (returns correct detail) ─────

    public function details(): Attribute
    {
        return Attribute::get(fn () => match ($this->rental_type) {
            'house' => $this->houseDetails,
            'car' => $this->carDetails,
            'commercial' => $this->commercialDetails,
            'room' => $this->roomDetails,
        });
    }

    // ── Price formatting ───────────────────────────

    public function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => '₹' . number_format($this->price_per_unit, 0)
            . ' / ' . $this->price_unit);
    }

    // ── Availability check ─────────────────────────

    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        // Check blocked dates
        $blocked = $this->blocked_dates ?? [];
        $range = collect(range(
            strtotime($checkIn),
            strtotime($checkOut),
            86400
        ))->map(fn ($ts) => date('Y-m-d', $ts));

        if ($range->intersect($blocked)->isNotEmpty()) {
            return false;
        }

        // Check existing bookings (exclude cancelled/rejected)
        $overlap = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->exists();

        return !$overlap;
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('rental_type', $type);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price_per_unit', [$min, $max]);
    }
}
```

### 3b. RentalBooking (state machine)

```php
// app/Models/RentalBooking.php

namespace App\Models;

use App\Enums\RentalBookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalBooking extends Model
{
    use SoftDeletes;

    protected $table = 'rental_bookings';

    protected $fillable = [
        'rental_listing_id', 'user_id', 'owner_id',
        'check_in', 'check_out', 'nights',
        'price_per_unit', 'subtotal', 'cleaning_fee',
        'security_deposit', 'service_fee', 'total_amount', 'currency',
        'status', 'payment_status', 'payment_method', 'payment_reference',
        'guest_message', 'host_message', 'booking_type',
        'cancellation_reason', 'cancelled_by', 'cancelled_at',
        'guests_count', 'special_requests', 'metadata',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'nights' => 'integer',
        'guests_count' => 'integer',
        'cancelled_at' => 'datetime',
        'special_requests' => 'array',
        'metadata' => 'array',
    ];

    // ── Relationships ──────────────────────────────

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RentalBookingStatusHistory::class, 'rental_booking_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(RentalReview::class, 'rental_booking_id');
    }

    // ── Status helpers ─────────────────────────────

    public function getStatusEnum(): RentalBookingStatus
    {
        return RentalBookingStatus::from($this->status);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    // ── Pricing ────────────────────────────────────

    public function calculateTotal(): float
    {
        $this->subtotal = $this->price_per_unit * $this->nights;
        $this->service_fee = $this->subtotal * 0.05; // 5% platform fee
        $this->total_amount = $this->subtotal + $this->cleaning_fee + $this->service_fee;
        return $this->total_amount;
    }
}
```

### 3c. Booking Status Enum (state machine)

```php
// app/Enums/RentalBookingStatus.php

namespace App\Enums;

enum RentalBookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case Completed = 'completed';
    case CancelledByGuest = 'cancelled_by_guest';
    case CancelledByHost = 'cancelled_by_host';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Disputed = 'disputed';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                'confirmed' => ['host', 'system'],
                'rejected' => ['host'],
                'expired' => ['system'],
                'cancelled_by_guest' => ['guest'],
            ],
            self::Confirmed => [
                'active' => ['system'], // auto on check-in date
                'cancelled_by_guest' => ['guest'],
                'cancelled_by_host' => ['host'],
            ],
            self::Active => [
                'completed' => ['system'], // auto on check-out date
                'disputed' => ['guest', 'host', 'admin'],
            ],
            self::Completed => [
                'disputed' => ['guest', 'host'],
            ],
            default => [], // terminal states
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return array_key_exists($to->value, $this->allowedTransitions());
    }

    public function canActorTransitionTo(self $to, string $actor): bool
    {
        return in_array($actor, $this->allowedTransitions()[$to->value] ?? []);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Confirmation',
            self::Confirmed => 'Confirmed',
            self::Active => 'Checked In',
            self::Completed => 'Completed',
            self::CancelledByGuest => 'Cancelled by Guest',
            self::CancelledByHost => 'Cancelled by Host',
            self::Rejected => 'Rejected by Host',
            self::Expired => 'Expired',
            self::Disputed => 'Disputed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'blue',
            self::Active => 'green',
            self::Completed => 'gray',
            self::CancelledByGuest => 'red',
            self::CancelledByHost => 'red',
            self::Rejected => 'red',
            self::Expired => 'gray',
            self::Disputed => 'orange',
        };
    }
}
```

---

## 4. SERVICES (Business Logic Layer)

### 4a. ListingService

```php
// app/Services/Rental/ListingService.php

namespace App\Services\Rental;

use App\Models\RentalListing;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class ListingService
{
    public function createListing(array $data, array $typeDetails): RentalListing
    {
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);

        $listing = RentalListing::create($data);

        // Create type-specific details
        match ($listing->rental_type) {
            'house' => $listing->houseDetails()->create($typeDetails),
            'car' => $listing->carDetails()->create($typeDetails),
            'commercial' => $listing->commercialDetails()->create($typeDetails),
            'room' => $listing->roomDetails()->create($typeDetails),
        };

        return $listing->load('details');
    }

    public function updateListing(RentalListing $listing, array $data, ?array $typeDetails = null): RentalListing
    {
        $listing->update($data);

        if ($typeDetails && $listing->details) {
            $listing->details->update($typeDetails);
        }

        return $listing->fresh('details');
    }

    public function uploadPhotos(RentalListing $listing, array $files): array
    {
        $photos = $listing->photos ?? [];

        foreach ($files as $file) {
            $path = $file->store('rental-listings/' . $listing->id, 'public');
            $photos[] = '/storage/' . $path;
        }

        $listing->update(['photos' => $photos]);
        return $photos;
    }

    public function search(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = RentalListing::active()
            ->with('details')
            ->withAvg('reviews', 'rating')
            ->withCount('bookings');

        if (!empty($filters['rental_type'])) {
            $query->ofType($filters['rental_type']);
        }

        if (!empty($filters['city'])) {
            $query->inCity($filters['city']);
        }

        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $min = $filters['min_price'] ?? 0;
            $max = $filters['max_price'] ?? 999999;
            $query->priceBetween($min, $max);
        }

        // Date availability filter
        if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
            $listingIds = $this->getAvailableListingIds($filters['check_in'], $filters['check_out'], $filters['rental_type'] ?? null);
            $query->whereIn('id', $listingIds);
        }

        // Sort
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    private function getAvailableListingIds(string $checkIn, string $checkOut, ?string $type): array
    {
        $query = RentalListing::active()
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereIn('status', ['pending', 'confirmed', 'active'])
                ->where('check_in', '<', $checkOut)
                ->where('check_out', '>', $checkIn)
            );

        if ($type) {
            $query->ofType($type);
        }

        return $query->pluck('id')->toArray();
    }
}
```

### 4b. BookingService (with state machine)

```php
// app/Services/Rental/BookingService.php

namespace App\Services\Rental;

use App\Enums\RentalBookingStatus;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Models\RentalBookingStatusHistory;
use App\Exceptions\BookingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function createBooking(
        RentalListing $listing,
        array $data,
    ): RentalBooking {
        // Validate availability
        if (!$listing->isAvailable($data['check_in'], $data['check_out'])) {
            throw new BookingException('Listing is not available for selected dates.');
        }

        // Calculate nights
        $nights = (int) Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
        if ($nights < 1) {
            throw new BookingException('Minimum stay is 1 night.');
        }

        return DB::transaction(function () use ($listing, $data, $nights) {
            $booking = RentalBooking::create([
                'rental_listing_id' => $listing->id,
                'user_id' => Auth::id(),
                'owner_id' => $listing->user_id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'nights' => $nights,
                'price_per_unit' => $listing->price_per_unit,
                'cleaning_fee' => $listing->cleaning_fee,
                'security_deposit' => $listing->security_deposit,
                'guests_count' => $data['guests_count'] ?? 1,
                'guest_message' => $data['guest_message'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'booking_type' => $listing->instant_booking ? 'instant' : 'request',
                'status' => $listing->instant_booking ? 'confirmed' : 'pending',
            ]);

            $booking->calculateTotal();
            $booking->save();

            // Record status history
            $this->recordStatusHistory($booking, null, $booking->status, 'guest');

            // Update listing stats
            $listing->increment('total_bookings');

            return $booking;
        });
    }

    public function transition(
        RentalBooking $booking,
        RentalBookingStatus $toStatus,
        string $actor,
        ?string $note = null,
    ): RentalBooking {
        return DB::transaction(function () use ($booking, $toStatus, $actor, $note) {
            $fresh = RentalBooking::lockForUpdate()->findOrFail($booking->id);
            $fromStatus = $fresh->getStatusEnum();

            if (!$fromStatus->canTransitionTo($toStatus)) {
                throw new BookingException(
                    "Invalid transition: {$fromStatus->value} → {$toStatus->value}"
                );
            }

            if (!$fromStatus->canActorTransitionTo($toStatus, $actor)) {
                throw new BookingException(
                    "Actor '{$actor}' not authorized for this transition."
                );
            }

            $updateData = ['status' => $toStatus->value];

            // Handle cancellation metadata
            if ($toStatus === RentalBookingStatus::CancelledByGuest) {
                $updateData['cancelled_by'] = 'guest';
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $note;
            } elseif ($toStatus === RentalBookingStatus::CancelledByHost) {
                $updateData['cancelled_by'] = 'host';
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $note;
            }

            $fresh->update($updateData);

            $this->recordStatusHistory($fresh, $fromStatus->value, $toStatus->value, $actor, $note);

            return $fresh;
        });
    }

    public function confirmBooking(RentalBooking $booking, string $hostMessage = null): RentalBooking
    {
        if ($hostMessage) {
            $booking->update(['host_message' => $hostMessage]);
        }
        return $this->transition($booking, RentalBookingStatus::Confirmed, 'host');
    }

    public function rejectBooking(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::Rejected, 'host', $reason);
    }

    public function cancelByGuest(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::CancelledByGuest, 'guest', $reason);
    }

    public function cancelByHost(RentalBooking $booking, string $reason): RentalBooking
    {
        return $this->transition($booking, RentalBookingStatus::CancelledByHost, 'host', $reason);
    }

    // ── Auto-status via scheduled command ──────────

    public function autoCheckIn(): int
    {
        // Confirmed bookings where check_in = today → active
        $bookings = RentalBooking::where('status', 'confirmed')
            ->whereDate('check_in', today())
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            $this->transition($booking, RentalBookingStatus::Active, 'system');
            $count++;
        }
        return $count;
    }

    public function autoCheckOut(): int
    {
        // Active bookings where check_out = today → completed
        $bookings = RentalBooking::where('status', 'active')
            ->whereDate('check_out', today())
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            $this->transition($booking, RentalBookingStatus::Completed, 'system');
            $count++;
        }
        return $count;
    }

    public function autoExpire(): int
    {
        // Pending bookings older than 48 hours → expired
        $bookings = RentalBooking::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            $this->transition($booking, RentalBookingStatus::Expired, 'system');
            $count++;
        }
        return $count;
    }

    private function recordStatusHistory(
        RentalBooking $booking,
        ?string $from,
        string $to,
        string $actor,
        ?string $note = null,
    ): void {
        RentalBookingStatusHistory::create([
            'rental_booking_id' => $booking->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => Auth::id(),
            'actor_type' => $actor,
            'note' => $note,
        ]);
    }
}
```

### 4c. AvailabilityService

```php
// app/Services/Rental/AvailabilityService.php

namespace App\Services\Rental;

use App\Models\RentalListing;
use App\Models\RentalAvailability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
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

    public function blockDates(RentalListing $listing, array $dates, string $reason = null): void
    {
        foreach ($dates as $date) {
            RentalAvailability::updateOrCreate(
                ['rental_listing_id' => $listing->id, 'date' => $date],
                ['status' => 'blocked', 'reason' => $reason]
            );
        }
    }

    public function unblockDates(RentalListing $listing, array $dates): void
    {
        RentalAvailability::where('rental_listing_id', $listing->id)
            ->whereIn('date', $dates)
            ->where('status', 'blocked')
            ->delete();
    }

    public function setPeakPricing(RentalListing $listing, array $dates, float $price): void
    {
        foreach ($dates as $date) {
            RentalAvailability::updateOrCreate(
                ['rental_listing_id' => $listing->id, 'date' => $date],
                ['status' => 'available', 'price_override' => $price]
            );
        }
    }

    public function getAvailableUnits(RentalListing $listing, string $checkIn, string $checkOut): int
    {
        if ($listing->rental_type !== 'room') return 0;

        $bookedRooms = $listing->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->sum('guests_count'); // or count distinct rooms

        return max(0, $listing->roomDetails->total_rooms - $bookedRooms);
    }
}
```

---

## 5. CONTROLLERS

### 5a. API Controller (for Flutter mobile)

```php
// app/Http/Controllers/Api/V1/RentalListingController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Rental\ListingService;
use App\Services\Rental\AvailabilityService;
use App\Models\RentalListing;
use Illuminate\Http\Request;

class RentalListingController extends Controller
{
    public function __construct(
        private ListingService $listingService,
        private AvailabilityService $availabilityService,
    ) {}

    // GET /api/v1/rentals?type=house&city=Chennai&check_in=2026-09-01&check_out=2026-09-05
    public function index(Request $request)
    {
        $validated = $request->validate([
            'rental_type' => 'nullable|in:house,car,commercial,room',
            'city' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'sort' => 'nullable|in:price_per_unit,created_at,avg_rating',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        $listings = $this->listingService->search($validated);

        return response()->json([
            'success' => true,
            'data' => $listings->items(),
            'meta' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ],
        ]);
    }

    // GET /api/v1/rentals/{listing}
    public function show(RentalListing $listing)
    {
        $listing->load(['details', 'owner:id,name,avatar', 'reviews.user:id,name,avatar']);

        return response()->json([
            'success' => true,
            'data' => $listing,
        ]);
    }

    // GET /api/v1/rentals/{listing}/calendar?month=2026-09
    public function calendar(RentalListing $listing, Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $calendar = $this->availabilityService->getCalendar($listing, $month);

        return response()->json([
            'success' => true,
            'data' => $calendar,
        ]);
    }

    // POST /api/v1/rentals (owner creates listing)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rental_type' => 'required|in:house,car,commercial,room',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price_per_unit' => 'required|numeric|min:1',
            'price_unit' => 'required|in:hour,day,month,year',
            'security_deposit' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'instant_booking' => 'nullable|boolean',
            // Type-specific details passed as nested object
            'details' => 'required|array',
        ]);

        $validated['user_id'] = auth()->id();

        $listing = $this->listingService->createListing(
            collect($validated)->except('details')->toArray(),
            $validated['details']
        );

        return response()->json([
            'success' => true,
            'data' => $listing,
        ], 201);
    }
}
```

### 5b. Booking API Controller

```php
// app/Http/Controllers/Api/V1/RentalBookingController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Rental\BookingService;
use App\Models\RentalBooking;
use App\Models\RentalListing;
use Illuminate\Http\Request;

class RentalBookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {}

    // POST /api/v1/rentals/{listing}/bookings
    public function store(Request $request, RentalListing $listing)
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'guest_message' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|array',
        ]);

        $booking = $this->bookingService->createBooking($listing, $validated);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ], 201);
    }

    // GET /api/v1/bookings/my (guest's bookings)
    public function myBookings(Request $request)
    {
        $bookings = RentalBooking::where('user_id', auth()->id())
            ->with(['listing:id,title,city,photos,rental_type', 'listing.details'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    // GET /api/v1/owner/bookings (host's bookings)
    public function ownerBookings(Request $request)
    {
        $bookings = RentalBooking::where('owner_id', auth()->id())
            ->with(['listing:id,title,city,photos,rental_type', 'guest:id,name,avatar,phone'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    // POST /api/v1/bookings/{booking}/confirm (host confirms)
    public function confirm(Request $request, RentalBooking $booking)
    {
        $this->authorize('confirm', $booking);

        $booking = $this->bookingService->confirmBooking(
            $booking,
            $request->get('host_message')
        );

        return response()->json(['success' => true, 'data' => $booking]);
    }

    // POST /api/v1/bookings/{booking}/reject (host rejects)
    public function reject(Request $request, RentalBooking $booking)
    {
        $this->authorize('reject', $booking);

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $booking = $this->bookingService->rejectBooking($booking, $validated['reason']);

        return response()->json(['success' => true, 'data' => $booking]);
    }

    // POST /api/v1/bookings/{booking}/cancel
    public function cancel(Request $request, RentalBooking $booking)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $booking = $this->bookingService->cancelByGuest($booking, $validated['reason']);

        return response()->json(['success' => true, 'data' => $booking]);
    }
}
```

---

## 6. ROUTES

```php
// routes/api.php — add inside Route::prefix('v1')->group(function () { ... })

// ── Rental Listings ────────────────────────────────
Route::get('rentals', [RentalListingController::class, 'index']);
Route::post('rentals', [RentalListingController::class, 'store']);
Route::get('rentals/{listing}', [RentalListingController::class, 'show']);
Route::put('rentals/{listing}', [RentalListingController::class, 'update']);
Route::delete('rentals/{listing}', [RentalListingController::class, 'destroy']);
Route::post('rentals/{listing}/photos', [RentalListingController::class, 'uploadPhotos']);
Route::get('rentals/{listing}/calendar', [RentalListingController::class, 'calendar']);

// ── Rental Bookings ────────────────────────────────
Route::post('rentals/{listing}/bookings', [RentalBookingController::class, 'store']);
Route::get('bookings/my', [RentalBookingController::class, 'myBookings']);
Route::get('owner/bookings', [RentalBookingController::class, 'ownerBookings']);
Route::get('bookings/{booking}', [RentalBookingController::class, 'show']);
Route::post('bookings/{booking}/confirm', [RentalBookingController::class, 'confirm']);
Route::post('bookings/{booking}/reject', [RentalBookingController::class, 'reject']);
Route::post('bookings/{booking}/cancel', [RentalBookingController::class, 'cancel']);

// ── Rental Reviews ─────────────────────────────────
Route::post('bookings/{booking}/review', [RentalReviewController::class, 'store']);
Route::get('rentals/{listing}/reviews', [RentalReviewController::class, 'index']);

// ── Owner Dashboard ────────────────────────────────
Route::get('owner/listings', [OwnerDashboardController::class, 'listings']);
Route::get('owner/stats', [OwnerDashboardController::class, 'stats']);
Route::get('owner/earnings', [OwnerDashboardController::class, 'earnings']);
```

---

## 7. SCHEDULED COMMANDS (Cron)

```php
// app/Console/Commands/RentalStatusCheck.php

namespace App\Console\Commands;

use App\Services\Rental\BookingService;
use Illuminate\Console\Command;

class RentalStatusCheck extends Command
{
    protected $signature = 'rental:status-check';
    protected $description = 'Auto-transition rental bookings (check-in, check-out, expiry)';

    public function handle(BookingService $bookingService): int
    {
        $checkedIn = $bookingService->autoCheckIn();
        $checkedOut = $bookingService->autoCheckOut();
        $expired = $bookingService->autoExpire();

        $this->info("Check-in: {$checkedIn}, Check-out: {$checkedOut}, Expired: {$expired}");

        return Command::SUCCESS;
    }
}
```

```php
// routes/console.php — schedule it
use App\Console\Commands\RentalStatusCheck;

Schedule::command('rental:status-check')->everyFifteenMinutes();
```

---

## 8. FILE STRUCTURE (Final)

```
app/
├── Console/Commands/
│   └── RentalStatusCheck.php
├── Enums/
│   └── RentalBookingStatus.php
├── Exceptions/
│   └── BookingException.php
├── Http/Controllers/Api/V1/
│   ├── RentalListingController.php
│   ├── RentalBookingController.php
│   ├── RentalReviewController.php
│   └── OwnerDashboardController.php
├── Http/Policies/
│   └── RentalBookingPolicy.php
├── Models/
│   ├── RentalListing.php
│   ├── RentalBooking.php
│   ├── RentalBookingStatusHistory.php
│   ├── RentalAvailability.php
│   ├── RentalReview.php
│   ├── HouseDetail.php
│   ├── CarDetail.php
│   ├── CommercialDetail.php
│   └── RoomDetail.php
└── Services/Rental/
    ├── ListingService.php
    ├── BookingService.php
    ├── AvailabilityService.php
    └── ReviewService.php

database/migrations/
├── 2026_08_09_create_rental_listings_table.php
├── 2026_08_09_create_house_details_table.php
├── 2026_08_09_create_car_details_table.php
├── 2026_08_09_create_commercial_details_table.php
├── 2026_08_09_create_room_details_table.php
├── 2026_08_09_create_rental_bookings_table.php
├── 2026_08_09_create_rental_booking_status_histories_table.php
├── 2026_08_09_create_rental_availability_table.php
└── 2026_08_09_create_rental_reviews_table.php
```

---

## 9. ADMIN PANEL (Vue3 + Inertia)

Add to existing sidebar:

```php
// Sidebar menu items
Route::middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/admin/rentals', [AdminRentalController::class, 'index'])->name('admin.rentals');
    Route::get('/admin/rentals/{listing}', [AdminRentalController::class, 'show'])->name('admin.rentals.show');
    Route::get('/admin/rentals-bookings', [AdminRentalBookingController::class, 'index'])->name('admin.rentals-bookings');
    Route::get('/admin/rentals-reviews', [AdminRentalReviewController::class, 'index'])->name('admin.rentals-reviews');
});
```

Vue pages:
```
resources/js/Pages/Admin/
├── Rentals/
│   ├── Index.vue          (list all, filter by type/status)
│   ├── Show.vue           (listing details + bookings)
│   └── Edit.vue           (admin edit)
├── RentalBookings/
│   ├── Index.vue          (all bookings, filter by status)
│   └── Show.vue           (booking details + status timeline)
└── RentalReviews/
    └── Index.vue          (all reviews, moderate)
```

---

## 10. FLUTTER APP (Mobile)

### API Service Structure:

```
lib/
├── features/rentals/
│   ├── data/
│   │   ├── rental_api_service.dart      (HTTP calls)
│   │   └── rental_models.dart           (JSON serialization)
│   ├── presentation/
│   │   ├── rental_list_page.dart         (search + filter)
│   │   ├── rental_detail_page.dart       (listing details + calendar)
│   │   ├── rental_booking_page.dart      (book now)
│   │   ├── my_bookings_page.dart         (guest bookings)
│   │   └── owner_bookings_page.dart      (host bookings)
│   └── widgets/
│       ├── rental_card.dart              (listing card)
│       ├── rental_filter_sheet.dart      (bottom sheet filters)
│       ├── availability_calendar.dart    (month calendar)
│       └── booking_status_badge.dart     (colored status)
```

### Key Flutter Pages:

1. **RentalListPage** — Search with filters (type, city, price range, dates)
2. **RentalDetailPage** — Photos carousel, details, calendar, book button
3. **MyBookingsPage** — List of guest's bookings with status tabs
4. **OwnerBookingsPage** — Host's incoming/active/completed bookings
5. **BookingDetailPage** — Full booking details, timeline, actions (confirm/reject/cancel)

---

## 11. PRICING STRATEGY BY TYPE

| Rental Type   | Price Unit   | Min Stay    | Security Deposit | Service Fee |
|---------------|-------------|-------------|------------------|-------------|
| House         | month       | 1 month     | 2 months rent    | 5%          |
| Car           | day/hour    | 1 day       | ₹2000-5000       | 10%         |
| Commercial    | month/year  | 11 months   | 6 months rent    | 3%          |
| Room (PG)     | month       | 1 month     | 1 month rent     | 5%          |
| Room (Hotel)  | day         | 1 night     | ₹1000            | 10%         |

---

## 12. PAYMENT FLOW

```
Guest Books → Payment Authorized (hold) → Host Confirms → Payment Captured
                                                          → Security Deposit Held
Guest Checks Out → Review Period (24h) → Security Deposit Released
                                      → Service Fee Settled to Owner
                                      → Platform Commission Retained

Cancellation Policy:
├── Guest cancels >48h before check-in → Full refund
├── Guest cancels 24-48h before check-in → 50% refund
├── Guest cancels <24h before check-in → No refund
├── Host cancels anytime → Full refund + penalty to host
└── No-show → No refund
```

---

## 13. IMPLEMENTATION ORDER

Phase 1 — Foundation (Week 1):
  1. Database migrations (all 9 tables)
  2. Eloquent models (all 9 + enums)
  3. ListingService + CRUD API
  4. Basic search/filter

Phase 2 — Bookings (Week 2):
  1. BookingService + state machine
  2. Availability calendar
  3. Scheduled status check command
  4. Booking API endpoints

Phase 3 — Reviews & Payments (Week 3):
  1. Review system
  2. Payment integration (Razorpay/Stripe)
  3. Cancellation policy
  4. Security deposit flow

Phase 4 — Admin & Portal (Week 4):
  1. Admin panel (Vue3 pages)
  2. Owner dashboard
  3. Earnings/settlement view

Phase 5 — Flutter Mobile (Week 5-6):
  1. Rental list + search
  2. Detail + calendar
  3. Booking flow
  4. My bookings / Owner bookings
  5. Reviews

Phase 6 — Polish (Week 7):
  1. Push notifications (booking confirmed, check-in reminder)
  2. Email notifications
  3. Analytics/reports
  4. SEO optimization

---

## 14. KEY DESIGN PRINCIPLES

1. **Single rental_type field** — One table, one codebase, filter by type
2. **Polymorphic details** — Type-specific data in separate tables
3. **State machine** — All status changes go through BookingService
4. **Availability calendar** — Prevent double-booking
5. **Scheduled jobs** — Auto check-in/check-out/expiry
6. **Service layer** — Controllers thin, logic in services
7. **Consistent API** — Same JSON envelope for all endpoints
8. **Flutter-first** — API designed for mobile consumption
9. **Admin visibility** — All data visible in admin panel
10. **Scalable** — Can add new types (parking, equipment) without touching existing code
