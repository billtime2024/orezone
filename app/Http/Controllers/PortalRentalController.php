<?php

namespace App\Http\Controllers;

use App\Models\RentalBooking;
use App\Models\RentalListing;
use App\Services\Rental\AvailabilityService;
use App\Services\Rental\BookingService;
use App\Services\Rental\ListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalRentalController extends Controller
{
    public function __construct(
        private ListingService $listingService,
        private BookingService $bookingService,
        private AvailabilityService $availabilityService,
    ) {}

    /**
     * GET /portal/rentals — Browse all active rentals.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'rental_type', 'city', 'min_price', 'max_price',
            'check_in', 'check_out', 'search', 'sort', 'direction',
        ]);

        $listings = $this->listingService->search($filters);

        return Inertia::render('portal/rentals/index', [
            'listings' => $listings,
            'filters' => $filters,
        ]);
    }

    /**
     * GET /portal/rentals/{listing} — Show listing details.
     */
    public function show(RentalListing $listing)
    {
        $listing = $this->listingService->getListing($listing);

        return Inertia::render('portal/rentals/show', [
            'listing' => $listing,
        ]);
    }

    /**
     * GET /portal/rentals/my — Owner's own listings.
     */
    public function myListings(Request $request)
    {
        $listings = RentalListing::where('user_id', $request->user()->id)
            ->with(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('portal/rentals/my-listings', [
            'listings' => $listings,
        ]);
    }

    /**
     * GET /portal/rentals/{listing}/calendar — Availability calendar.
     */
    public function calendar(Request $request, RentalListing $listing)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $calendar = $this->availabilityService->getCalendar($listing, $month);

        return response()->json([
            'success' => true,
            'data' => $calendar,
        ]);
    }

    /**
     * POST /portal/rentals/{listing}/bookings — Create a booking.
     */
    public function storeBooking(Request $request, RentalListing $listing)
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'guest_message' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|array',
        ]);

        try {
            $booking = $this->bookingService->createBooking($listing, $validated);

            return redirect()->route('portal.rentals-bookings')
                ->with('success', 'Booking created successfully!');
        } catch (\App\Exceptions\BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * GET /portal/rentals-bookings — Guest's bookings.
     */
    public function myBookings(Request $request)
    {
        $bookings = RentalBooking::where('user_id', $request->user()->id)
            ->with(['listing:id,title,city,photos,rental_type', 'listing.houseDetails', 'listing.carDetails', 'listing.commercialDetails', 'listing.roomDetails'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('portal/rentals/my-bookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * GET /portal/owner/rentals-bookings — Host's bookings.
     */
    public function ownerBookings(Request $request)
    {
        $bookings = RentalBooking::where('owner_id', $request->user()->id)
            ->with(['listing:id,title,city,photos,rental_type', 'guest:id,name,avatar_path,phone'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('portal/rentals/owner-bookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * POST /portal/rentals-bookings/{booking}/confirm — Host confirms.
     */
    public function confirmBooking(Request $request, RentalBooking $booking)
    {
        if ($booking->owner_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->bookingService->confirmBooking($booking);
            return back()->with('success', 'Booking confirmed!');
        } catch (\App\Exceptions\BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * POST /portal/rentals-bookings/{booking}/reject — Host rejects.
     */
    public function rejectBooking(Request $request, RentalBooking $booking)
    {
        if ($booking->owner_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->bookingService->rejectBooking($booking, $validated['reason']);
            return back()->with('success', 'Booking rejected.');
        } catch (\App\Exceptions\BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * POST /portal/rentals-bookings/{booking}/cancel — Guest cancels.
     */
    public function cancelBooking(Request $request, RentalBooking $booking)
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->bookingService->cancelByGuest($booking, $validated['reason']);
            return back()->with('success', 'Booking cancelled.');
        } catch (\App\Exceptions\BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * POST /portal/rentals-bookings/{booking}/host-cancel — Host cancels.
     */
    public function hostCancelBooking(Request $request, RentalBooking $booking)
    {
        if ($booking->owner_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->bookingService->cancelByHost($booking, $validated['reason']);
            return back()->with('success', 'Booking cancelled by host.');
        } catch (\App\Exceptions\BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    // ── CRUD: Create Listing ────────────────────────────────────

    /**
     * GET /portal/rentals/create — Show create form.
     */
    public function create()
    {
        return Inertia::render('portal/rentals/create');
    }

    /**
     * POST /portal/rentals — Store new listing.
     */
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
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'instant_booking' => 'nullable|boolean',
            'rules' => 'nullable|array',
            'rules.*' => 'string|max:255',
            'details' => 'required|array',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $data = collect($validated)->except(['details', 'photos'])->toArray();
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'active';

        $listing = $this->listingService->createListing($data, $validated['details']);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $this->listingService->uploadPhotos($listing, $request->file('photos'));
        }

        return redirect()->route('portal.rentals.my')
            ->with('success', 'Listing created successfully!');
    }

    // ── CRUD: Edit Listing ──────────────────────────────────────

    /**
     * GET /portal/rentals/{listing}/edit — Show edit form.
     */
    public function edit(RentalListing $listing)
    {
        if ($listing->user_id !== auth()->user()?->id) {
            abort(403);
        }

        $listing->load(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails']);

        return Inertia::render('portal/rentals/edit', [
            'listing' => $listing,
        ]);
    }

    /**
     * PUT /portal/rentals/{listing} — Update listing.
     */
    public function update(Request $request, RentalListing $listing)
    {
        if ($listing->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price_per_unit' => 'sometimes|numeric|min:1',
            'price_unit' => 'sometimes|in:hour,day,month,year',
            'security_deposit' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'pincode' => 'sometimes|string|max:10',
            'instant_booking' => 'nullable|boolean',
            'rules' => 'nullable|array',
            'details' => 'nullable|array',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_photos' => 'nullable|array',
            'remove_photos.*' => 'string',
        ]);

        $data = collect($validated)->except(['details', 'photos', 'remove_photos'])->toArray();
        $details = $validated['details'] ?? null;

        $this->listingService->updateListing($listing, $data, $details);

        // Handle photo removals
        if (!empty($validated['remove_photos'])) {
            foreach ($validated['remove_photos'] as $photoUrl) {
                $this->listingService->deletePhoto($listing, $photoUrl);
            }
        }

        // Handle new photo uploads
        if ($request->hasFile('photos')) {
            $this->listingService->uploadPhotos($listing, $request->file('photos'));
        }

        return redirect()->route('portal.rentals.my')
            ->with('success', 'Listing updated successfully!');
    }

    // ── CRUD: Delete Listing ────────────────────────────────────

    /**
     * DELETE /portal/rentals/{listing} — Delete listing.
     */
    public function destroy(Request $request, RentalListing $listing)
    {
        if ($listing->user_id !== $request->user()->id) {
            abort(403);
        }

        $hasActive = $listing->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['error' => 'Cannot delete listing with active bookings.']);
        }

        $listing->delete();

        return redirect()->route('portal.rentals.my')
            ->with('success', 'Listing deleted successfully.');
    }

    // ── CRUD: Toggle Status ─────────────────────────────────────

    /**
     * POST /portal/rentals/{listing}/toggle-status — Toggle active/inactive.
     */
    public function toggleStatus(Request $request, RentalListing $listing)
    {
        if ($listing->user_id !== $request->user()->id) {
            abort(403);
        }

        $newStatus = $listing->status === 'active' ? 'paused' : 'active';
        $listing->update(['status' => $newStatus]);

        return back()->with('success', "Listing {$newStatus} successfully!");
    }
}
