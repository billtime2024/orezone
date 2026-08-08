<?php

namespace Database\Seeders;

use App\Models\RentalListing;
use App\Models\HouseDetail;
use App\Models\CarDetail;
use App\Models\CommercialDetail;
use App\Models\RoomDetail;
use App\Models\RentalBooking;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users or create sample ones
        $users = User::all();
        if ($users->count() < 2) {
            return;
        }

        $owner1 = $users[0];
        $owner2 = $users[1];
        $guest = $users->last();

        // ══════════════════════════════════════════════════════════
        // HOUSE RENTALS
        // ══════════════════════════════════════════════════════════

        $house1 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'house',
            'title' => 'Spacious 3BHK Apartment in Anna Nagar',
            'description' => 'Beautiful 3BHK apartment in the heart of Anna Nagar. Close to metro, shopping malls, and restaurants. Fully furnished with modern amenities. Ideal for families.',
            'slug' => Str::slug('spacious-3bhk-anna-nagar') . '-' . Str::random(6),
            'price_per_unit' => 25000,
            'price_unit' => 'month',
            'security_deposit' => 50000,
            'cleaning_fee' => 1500,
            'address_line1' => '45, 2nd Avenue, Anna Nagar West',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600040',
            'latitude' => 13.0843,
            'longitude' => 80.2081,
            'status' => 'active',
            'instant_booking' => false,
            'photos' => ['/storage/rental-listings/house1-1.jpg', '/storage/rental-listings/house1-2.jpg'],
            'rules' => ['No smoking', 'No pets', 'No loud music after 10 PM', 'Monthly rent due by 5th'],
            'total_bookings' => 3,
            'avg_rating' => 4.5,
            'review_count' => 2,
        ]);

        HouseDetail::create([
            'rental_listing_id' => $house1->id,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 1,
            'furnished' => true,
            'parking' => true,
            'ac' => true,
            'wifi' => true,
            'amenities' => ['washing_machine', 'gym', 'swimming_pool', 'power_backup', 'lift', '24x7_security'],
            'property_type' => 'apartment',
            'area_sqft' => 1400,
        ]);

        $house2 = RentalListing::create([
            'user_id' => $owner2->id,
            'rental_type' => 'house',
            'title' => 'Independent House with Garden in Velachery',
            'description' => 'Independent house with a beautiful garden. Spacious rooms, natural ventilation, and a peaceful neighborhood. Perfect for families who love outdoor space.',
            'slug' => Str::slug('independent-house-velachery') . '-' . Str::random(6),
            'price_per_unit' => 35000,
            'price_unit' => 'month',
            'security_deposit' => 70000,
            'cleaning_fee' => 2000,
            'address_line1' => '12, 3rd Cross Street, Velachery',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600042',
            'latitude' => 12.9815,
            'longitude' => 80.2180,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/house2-1.jpg'],
            'rules' => ['No smoking', 'Pets allowed with deposit', 'Garden maintenance included'],
            'total_bookings' => 1,
            'avg_rating' => 4.8,
            'review_count' => 1,
        ]);

        HouseDetail::create([
            'rental_listing_id' => $house2->id,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'floors' => 2,
            'furnished' => false,
            'parking' => true,
            'ac' => true,
            'wifi' => false,
            'amenities' => ['garden', 'parking', 'servant_room', 'store_room'],
            'property_type' => 'independent_house',
            'area_sqft' => 2200,
        ]);

        $house3 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'house',
            'title' => '1BHK Studio Apartment near OMR',
            'description' => 'Compact and cozy 1BHK studio perfect for working professionals. Walking distance to IT parks. Includes all basic amenities.',
            'slug' => Str::slug('1bhk-studio-omr') . '-' . Str::random(6),
            'price_per_unit' => 12000,
            'price_unit' => 'month',
            'security_deposit' => 24000,
            'cleaning_fee' => 800,
            'address_line1' => '78, OMR Road, Thoraipakkam',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600097',
            'latitude' => 12.9416,
            'longitude' => 80.2297,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/house3-1.jpg'],
            'rules' => ['No smoking', 'No pets', 'Visitors allowed till 9 PM'],
            'total_bookings' => 5,
            'avg_rating' => 4.2,
            'review_count' => 3,
        ]);

        HouseDetail::create([
            'rental_listing_id' => $house3->id,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'floors' => 1,
            'furnished' => true,
            'parking' => false,
            'ac' => true,
            'wifi' => true,
            'amenities' => ['furnished', 'wifi', 'ac', 'gym'],
            'property_type' => 'apartment',
            'area_sqft' => 650,
        ]);

        // ══════════════════════════════════════════════════════════
        // CAR RENTALS
        // ══════════════════════════════════════════════════════════

        $car1 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'car',
            'title' => 'Maruti Suzuki Swift — Self Drive',
            'description' => 'Well-maintained Maruti Swift for daily rental. Perfect for city commute and weekend trips. AC, power steering, and music system.',
            'slug' => Str::slug('maruti-swift-self-drive') . '-' . Str::random(6),
            'price_per_unit' => 1200,
            'price_unit' => 'day',
            'security_deposit' => 3000,
            'cleaning_fee' => 200,
            'address_line1' => '23, T Nagar',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600017',
            'latitude' => 13.0418,
            'longitude' => 80.2341,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/car1-1.jpg'],
            'rules' => ['Valid driving license required', 'Fuel not included', 'Return with same fuel level', 'No smoking in car'],
            'total_bookings' => 8,
            'avg_rating' => 4.6,
            'review_count' => 5,
        ]);

        CarDetail::create([
            'rental_listing_id' => $car1->id,
            'make' => 'Maruti Suzuki',
            'model' => 'Swift',
            'year' => 2023,
            'color' => 'White',
            'fuel_type' => 'petrol',
            'transmission' => 'manual',
            'seats' => 5,
            'self_drive' => true,
            'with_driver' => false,
            'driver_charge_per_day' => 0,
            'mileage_km' => 15000,
            'registration_number' => 'TN09AB1234',
            'insurance_details' => ['type' => 'comprehensive', 'valid_until' => '2027-03-15'],
            'documents' => ['rc', 'insurance', 'puc'],
        ]);

        $car2 = RentalListing::create([
            'user_id' => $owner2->id,
            'rental_type' => 'car',
            'title' => 'Toyota Innova Crysta — With Driver',
            'description' => 'Premium Toyota Innova Crysta with experienced driver. Ideal for airport transfers, family outings, and outstation trips.',
            'slug' => Str::slug('toyota-innova-with-driver') . '-' . Str::random(6),
            'price_per_unit' => 3500,
            'price_unit' => 'day',
            'security_deposit' => 5000,
            'cleaning_fee' => 500,
            'address_line1' => '56, Adyar',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600020',
            'latitude' => 13.0067,
            'longitude' => 80.2569,
            'status' => 'active',
            'instant_booking' => false,
            'photos' => ['/storage/rental-listings/car2-1.jpg'],
            'rules' => ['Driver included', 'Fuel included within city', 'Outstation charges extra', 'Night charge applicable after 10 PM'],
            'total_bookings' => 12,
            'avg_rating' => 4.9,
            'review_count' => 8,
        ]);

        CarDetail::create([
            'rental_listing_id' => $car2->id,
            'make' => 'Toyota',
            'model' => 'Innova Crysta',
            'year' => 2024,
            'color' => 'Silver',
            'fuel_type' => 'diesel',
            'transmission' => 'automatic',
            'seats' => 7,
            'self_drive' => false,
            'with_driver' => true,
            'driver_charge_per_day' => 0,
            'mileage_km' => 8000,
            'registration_number' => 'TN01CD5678',
            'insurance_details' => ['type' => 'comprehensive', 'valid_until' => '2027-06-20'],
            'documents' => ['rc', 'insurance', 'puc', 'driver_badge'],
        ]);

        $car3 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'car',
            'title' => 'Hyundai Creta — Hourly Rental',
            'description' => 'Hyundai Creta available for hourly rental. Perfect for short trips and errands. Minimum 4-hour booking.',
            'slug' => Str::slug('hyundai-creta-hourly') . '-' . Str::random(6),
            'price_per_unit' => 200,
            'price_unit' => 'hour',
            'security_deposit' => 3000,
            'cleaning_fee' => 200,
            'address_line1' => '89, Porur',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600125',
            'latitude' => 13.0358,
            'longitude' => 80.1583,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/car3-1.jpg'],
            'rules' => ['Valid driving license required', 'Minimum 4 hours', 'Fuel not included', 'GPS tracker installed'],
            'total_bookings' => 15,
            'avg_rating' => 4.3,
            'review_count' => 10,
        ]);

        CarDetail::create([
            'rental_listing_id' => $car3->id,
            'make' => 'Hyundai',
            'model' => 'Creta',
            'year' => 2024,
            'color' => 'Black',
            'fuel_type' => 'petrol',
            'transmission' => 'automatic',
            'seats' => 5,
            'self_drive' => true,
            'with_driver' => false,
            'driver_charge_per_day' => 0,
            'mileage_km' => 5000,
            'registration_number' => 'TN07EF9012',
            'insurance_details' => ['type' => 'comprehensive', 'valid_until' => '2027-01-10'],
            'documents' => ['rc', 'insurance', 'puc'],
        ]);

        // ══════════════════════════════════════════════════════════
        // COMMERCIAL BUILDING RENTALS
        // ══════════════════════════════════════════════════════════

        $comm1 = RentalListing::create([
            'user_id' => $owner2->id,
            'rental_type' => 'commercial',
            'title' => 'Grade A Office Space in Guindy',
            'description' => 'Premium office space in Guindy industrial area. Suitable for IT companies, startups, and corporate offices. Ready to move in with modular furniture.',
            'slug' => Str::slug('grade-a-office-guindy') . '-' . Str::random(6),
            'price_per_unit' => 55000,
            'price_unit' => 'month',
            'security_deposit' => 275000,
            'cleaning_fee' => 5000,
            'address_line1' => '15, Guindy Industrial Estate',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600032',
            'latitude' => 13.0063,
            'longitude' => 80.2206,
            'status' => 'active',
            'instant_booking' => false,
            'photos' => ['/storage/rental-listings/comm1-1.jpg'],
            'rules' => ['Commercial use only', 'Maintenance charges extra', 'Minimum 11 months lease', 'No subletting without approval'],
            'total_bookings' => 2,
            'avg_rating' => 4.7,
            'review_count' => 2,
        ]);

        CommercialDetail::create([
            'rental_listing_id' => $comm1->id,
            'property_type' => 'office',
            'area_sqft' => 2500,
            'carpet_area_sqft' => 2000,
            'furnished' => true,
            'ac' => true,
            'power_backup' => true,
            'parking' => true,
            'parking_slots' => 10,
            'floor_number' => 3,
            'total_floors' => 5,
            'lift' => true,
            'facilities' => ['cctv', 'security', 'conference_room', 'server_room', 'pantry', 'reception'],
            'maintenance_charge' => 8000,
            'lease_type' => 'fully_furnished',
        ]);

        $comm2 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'commercial',
            'title' => 'Retail Shop Space in T Nagar',
            'description' => 'Prime retail shop space in T Nagar. High footfall area, ideal for clothing, electronics, or food business. Ground floor with display window.',
            'slug' => Str::slug('retail-shop-t-nagar') . '-' . Str::random(6),
            'price_per_unit' => 45000,
            'price_unit' => 'month',
            'security_deposit' => 225000,
            'cleaning_fee' => 3000,
            'address_line1' => '22, Usman Road, T Nagar',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600017',
            'latitude' => 13.0405,
            'longitude' => 80.2336,
            'status' => 'active',
            'instant_booking' => false,
            'photos' => ['/storage/rental-listings/comm2-1.jpg'],
            'rules' => ['Commercial use only', 'No modification without approval', 'Electricity bill extra', 'Signage board allowed'],
            'total_bookings' => 1,
            'avg_rating' => 4.4,
            'review_count' => 1,
        ]);

        CommercialDetail::create([
            'rental_listing_id' => $comm2->id,
            'property_type' => 'shop',
            'area_sqft' => 800,
            'carpet_area_sqft' => 700,
            'furnished' => false,
            'ac' => false,
            'power_backup' => false,
            'parking' => false,
            'parking_slots' => 0,
            'floor_number' => 1,
            'total_floors' => 3,
            'lift' => false,
            'facilities' => ['display_window', 'storage_room'],
            'maintenance_charge' => 3000,
            'lease_type' => 'bare_shell',
        ]);

        $comm3 = RentalListing::create([
            'user_id' => $owner2->id,
            'rental_type' => 'commercial',
            'title' => 'Co-working Space in Sholinganallur',
            'description' => 'Modern co-working space with hot desks, dedicated desks, and private cabins. High-speed internet, meeting rooms, and cafeteria included.',
            'slug' => Str::slug('co-working-sholinganallur') . '-' . Str::random(6),
            'price_per_unit' => 8000,
            'price_unit' => 'month',
            'security_deposit' => 16000,
            'cleaning_fee' => 1000,
            'address_line1' => '45, Sholinganallur IT Corridor',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600119',
            'latitude' => 12.9010,
            'longitude' => 80.2279,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/comm3-1.jpg'],
            'rules' => ['No smoking', 'Quiet hours 10 AM - 4 PM', 'Meeting room booking required', 'WiFi password shared on check-in'],
            'total_bookings' => 20,
            'avg_rating' => 4.6,
            'review_count' => 15,
        ]);

        CommercialDetail::create([
            'rental_listing_id' => $comm3->id,
            'property_type' => 'co_working',
            'area_sqft' => 1500,
            'carpet_area_sqft' => 1200,
            'furnished' => true,
            'ac' => true,
            'power_backup' => true,
            'parking' => true,
            'parking_slots' => 5,
            'floor_number' => 4,
            'total_floors' => 6,
            'lift' => true,
            'facilities' => ['cctv', 'security', 'cafeteria', 'meeting_room', 'high_speed_wifi', 'printer'],
            'maintenance_charge' => 2000,
            'lease_type' => 'fully_furnished',
        ]);

        // ══════════════════════════════════════════════════════════
        // ROOM STAY RENTALS
        // ══════════════════════════════════════════════════════════

        $room1 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'room',
            'title' => 'Premium PG for Men — Nungambakkam',
            'description' => 'Premium paying guest accommodation for men. Clean rooms, homely food, Wi-Fi, and laundry service. Walking distance to metro station.',
            'slug' => Str::slug('premium-pg-men-nungambakkam') . '-' . Str::random(6),
            'price_per_unit' => 10000,
            'price_unit' => 'month',
            'security_deposit' => 10000,
            'cleaning_fee' => 0,
            'address_line1' => '34, Nungambakkam High Road',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600034',
            'latitude' => 13.0604,
            'longitude' => 80.2496,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/room1-1.jpg'],
            'rules' => ['No smoking', 'No alcohol', 'Visitor hours 8 AM - 8 PM', 'ID proof mandatory', 'Food timings fixed'],
            'total_bookings' => 25,
            'avg_rating' => 4.3,
            'review_count' => 18,
        ]);

        RoomDetail::create([
            'rental_listing_id' => $room1->id,
            'room_type' => 'single',
            'stay_type' => 'pg',
            'meals_included' => true,
            'meal_plan' => 'full_board',
            'ac' => true,
            'wifi' => true,
            'laundry' => true,
            'housekeeping' => true,
            'curfew_time' => true,
            'check_in_time' => '08:00',
            'check_out_time' => '23:00',
            'rules' => ['No smoking', 'No alcohol', 'Visitor hours 8 AM - 8 PM'],
            'common_areas' => ['tv_lounge', 'kitchen', 'terrace', 'parking'],
            'total_rooms' => 20,
            'available_rooms' => 5,
        ]);

        $room2 = RentalListing::create([
            'user_id' => $owner2->id,
            'rental_type' => 'room',
            'title' => 'Budget Hostel for Students — Chrompet',
            'description' => 'Affordable hostel accommodation for students. Shared rooms with study tables. WiFi, common kitchen, and laundry available.',
            'slug' => Str::slug('budget-hostel-students-chrompet') . '-' . Str::random(6),
            'price_per_unit' => 5000,
            'price_unit' => 'month',
            'security_deposit' => 5000,
            'cleaning_fee' => 0,
            'address_line1' => '67, Grand Southern Trunk Road, Chrompet',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600044',
            'latitude' => 12.9516,
            'longitude' => 80.1462,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/room2-1.jpg'],
            'rules' => ['No smoking', 'Study hours 8 PM - 10 PM', 'ID proof mandatory', 'Monthly payment by 5th'],
            'total_bookings' => 30,
            'avg_rating' => 4.0,
            'review_count' => 22,
        ]);

        RoomDetail::create([
            'rental_listing_id' => $room2->id,
            'room_type' => 'triple',
            'stay_type' => 'hostel',
            'meals_included' => false,
            'meal_plan' => 'none',
            'ac' => false,
            'wifi' => true,
            'laundry' => true,
            'housekeeping' => false,
            'curfew_time' => true,
            'check_in_time' => '06:00',
            'check_out_time' => '22:00',
            'rules' => ['No smoking', 'Study hours 8 PM - 10 PM'],
            'common_areas' => ['study_room', 'kitchen', 'parking'],
            'total_rooms' => 30,
            'available_rooms' => 12,
        ]);

        $room3 = RentalListing::create([
            'user_id' => $owner1->id,
            'rental_type' => 'room',
            'title' => 'Luxury Hotel Room — Besant Nagar',
            'description' => 'Premium hotel room with sea view. Luxury amenities, 24/7 room service, and spa access. Perfect for short stays and vacations.',
            'slug' => Str::slug('luxury-hotel-besant-nagar') . '-' . Str::random(6),
            'price_per_unit' => 4500,
            'price_unit' => 'day',
            'security_deposit' => 5000,
            'cleaning_fee' => 500,
            'address_line1' => '12, Besant Nagar Beach Road',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600090',
            'latitude' => 12.9987,
            'longitude' => 80.2676,
            'status' => 'active',
            'instant_booking' => true,
            'photos' => ['/storage/rental-listings/room3-1.jpg'],
            'rules' => ['Check-in 2 PM', 'Check-out 11 AM', 'ID proof mandatory', 'No smoking in room'],
            'total_bookings' => 40,
            'avg_rating' => 4.8,
            'review_count' => 35,
        ]);

        RoomDetail::create([
            'rental_listing_id' => $room3->id,
            'room_type' => 'suite',
            'stay_type' => 'hotel',
            'meals_included' => false,
            'meal_plan' => 'breakfast',
            'ac' => true,
            'wifi' => true,
            'laundry' => true,
            'housekeeping' => true,
            'curfew_time' => false,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'rules' => ['Check-in 2 PM', 'Check-out 11 AM'],
            'common_areas' => ['restaurant', 'spa', 'pool', 'gym', 'parking'],
            'total_rooms' => 50,
            'available_rooms' => 20,
        ]);

        // ══════════════════════════════════════════════════════════
        // SAMPLE BOOKINGS
        // ══════════════════════════════════════════════════════════

        // House booking
        RentalBooking::create([
            'rental_listing_id' => $house1->id,
            'user_id' => $guest->id,
            'owner_id' => $owner1->id,
            'check_in' => now()->addDays(10)->format('Y-m-d'),
            'check_out' => now()->addDays(40)->format('Y-m-d'),
            'nights' => 30,
            'price_per_unit' => 25000,
            'subtotal' => 25000,
            'cleaning_fee' => 1500,
            'security_deposit' => 50000,
            'service_fee' => 1250,
            'total_amount' => 27750,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'booking_type' => 'request',
            'guest_message' => 'Looking for a long-term rental for my family.',
            'guests_count' => 4,
        ]);

        // Car booking
        RentalBooking::create([
            'rental_listing_id' => $car1->id,
            'user_id' => $guest->id,
            'owner_id' => $owner1->id,
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(7)->format('Y-m-d'),
            'nights' => 2,
            'price_per_unit' => 1200,
            'subtotal' => 2400,
            'cleaning_fee' => 200,
            'security_deposit' => 3000,
            'service_fee' => 120,
            'total_amount' => 2720,
            'status' => 'pending',
            'payment_status' => 'pending',
            'booking_type' => 'instant',
            'guests_count' => 1,
        ]);

        // Commercial booking
        RentalBooking::create([
            'rental_listing_id' => $comm3->id,
            'user_id' => $guest->id,
            'owner_id' => $owner2->id,
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(31)->format('Y-m-d'),
            'nights' => 30,
            'price_per_unit' => 8000,
            'subtotal' => 8000,
            'cleaning_fee' => 1000,
            'security_deposit' => 16000,
            'service_fee' => 400,
            'total_amount' => 9400,
            'status' => 'active',
            'payment_status' => 'captured',
            'booking_type' => 'instant',
            'guests_count' => 1,
        ]);

        // Room booking
        RentalBooking::create([
            'rental_listing_id' => $room3->id,
            'user_id' => $guest->id,
            'owner_id' => $owner1->id,
            'check_in' => now()->addDays(3)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'nights' => 2,
            'price_per_unit' => 4500,
            'subtotal' => 9000,
            'cleaning_fee' => 500,
            'security_deposit' => 5000,
            'service_fee' => 450,
            'total_amount' => 9950,
            'status' => 'completed',
            'payment_status' => 'captured',
            'booking_type' => 'instant',
            'guests_count' => 2,
        ]);

        $this->command->info("✅ RentalSeeder completed!");
        $this->command->info("   - 3 House listings created");
        $this->command->info("   - 3 Car listings created");
        $this->command->info("   - 3 Commercial listings created");
        $this->command->info("   - 3 Room listings created");
        $this->command->info("   - 4 Sample bookings created");
    }
}
