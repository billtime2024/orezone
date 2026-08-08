<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RentalListing;
use App\Services\Rental\AvailabilityService;
use App\Services\Rental\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalListingController extends Controller
{
    public function __construct(
        private ListingService $listingService,
        private AvailabilityService $availabilityService,
    ) {}

    /**
     * GET /rentals — Search listings with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rental_type' => 'nullable|in:house,car,commercial,room',
            'city' => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|in:price_per_unit,created_at,reviews_avg_rating,total_bookings',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
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

    /**
     * POST /rentals — Create a new listing.
     */
    public function store(Request $request): JsonResponse
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'instant_booking' => 'nullable|boolean',
            'blocked_dates' => 'nullable|array',
            'blocked_dates.*' => 'date',
            'rules' => 'nullable|array',
            'rules.*' => 'string|max:255',
            'details' => 'required|array',
        ]);

        $data = collect($validated)->except('details')->toArray();
        $data['user_id'] = $request->user()->id;

        $listing = $this->listingService->createListing($data, $validated['details']);

        return response()->json([
            'success' => true,
            'data' => $listing,
        ], 201);
    }

    /**
     * GET /rentals/{listing} — Show listing details.
     */
    public function show(RentalListing $listing): JsonResponse
    {
        $listing = $this->listingService->getListing($listing);

        return response()->json([
            'success' => true,
            'data' => $listing,
        ]);
    }

    /**
     * PUT /rentals/{listing} — Update a listing.
     */
    public function update(Request $request, RentalListing $listing): JsonResponse
    {
        // Only owner can update
        if ($listing->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => 'You can only update your own listings.',
            ], 403);
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'sometimes|in:draft,active,paused,closed',
            'instant_booking' => 'nullable|boolean',
            'blocked_dates' => 'nullable|array',
            'rules' => 'nullable|array',
            'details' => 'nullable|array',
        ]);

        $data = collect($validated)->except('details')->toArray();
        $details = $validated['details'] ?? null;

        $listing = $this->listingService->updateListing($listing, $data, $details);

        return response()->json([
            'success' => true,
            'data' => $listing,
        ]);
    }

    /**
     * DELETE /rentals/{listing} — Delete a listing.
     */
    public function destroy(Request $request, RentalListing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => 'You can only delete your own listings.',
            ], 403);
        }

        if (!$this->listingService->deleteListing($listing)) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot delete listing with active bookings.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully.',
        ]);
    }

    /**
     * POST /rentals/{listing}/photos — Upload photos.
     */
    public function uploadPhotos(Request $request, RentalListing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $photos = $this->listingService->uploadPhotos($listing, $request->file('photos'));

        return response()->json([
            'success' => true,
            'data' => ['photos' => $photos],
        ]);
    }

    /**
     * DELETE /rentals/{listing}/photos — Delete a photo.
     */
    public function deletePhoto(Request $request, RentalListing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $request->validate(['photo_url' => 'required|url']);

        $this->listingService->deletePhoto($listing, $request->photo_url);

        return response()->json([
            'success' => true,
            'data' => ['photos' => $listing->fresh()->photos],
        ]);
    }

    /**
     * GET /rentals/{listing}/calendar — Get availability calendar.
     */
    public function calendar(Request $request, RentalListing $listing): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        $calendar = $this->availabilityService->getCalendar($listing, $month);

        return response()->json([
            'success' => true,
            'data' => $calendar,
        ]);
    }

    /**
     * POST /rentals/{listing}/block-dates — Block dates.
     */
    public function blockDates(Request $request, RentalListing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->availabilityService->blockDates($listing, $validated['dates'], $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Dates blocked successfully.',
        ]);
    }

    /**
     * POST /rentals/{listing}/unblock-dates — Unblock dates.
     */
    public function unblockDates(Request $request, RentalListing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'date',
        ]);

        $this->availabilityService->unblockDates($listing, $validated['dates']);

        return response()->json([
            'success' => true,
            'message' => 'Dates unblocked successfully.',
        ]);
    }

    /**
     * GET /my-listings — Owner's own listings.
     */
    public function myListings(Request $request): JsonResponse
    {
        $listings = RentalListing::where('user_id', $request->user()->id)
            ->with(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

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
}
