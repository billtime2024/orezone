<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRentalListingRequest;
use App\Http\Requests\Api\V1\UpdateRentalListingRequest;
use App\Http\Resources\Api\V1\RentalListingResource;
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
            'data' => RentalListingResource::collection($listings),
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
    public function store(StoreRentalListingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $data = collect($validated)->except('details')->toArray();
        $data['user_id'] = $request->user()->id;

        $listing = $this->listingService->createListing($data, $validated['details']);

        return response()->json([
            'success' => true,
            'data' => new RentalListingResource($listing),
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
            'data' => new RentalListingResource($listing),
        ]);
    }

    /**
     * PUT /rentals/{listing} — Update a listing.
     */
    public function update(UpdateRentalListingRequest $request, RentalListing $listing): JsonResponse
    {
        $validated = $request->validated();

        $data = collect($validated)->except('details')->toArray();
        $details = $validated['details'] ?? null;

        $listing = $this->listingService->updateListing($listing, $data, $details);

        return response()->json([
            'success' => true,
            'data' => new RentalListingResource($listing),
        ]);
    }

    /**
     * DELETE /rentals/{listing} — Delete a listing.
     */
    public function destroy(RentalListing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

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
        $this->authorize('uploadPhotos', $listing);

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
        $this->authorize('deletePhoto', $listing);

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
        $this->authorize('manageDates', $listing);

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
        $this->authorize('manageDates', $listing);

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
            'data' => RentalListingResource::collection($listings),
            'meta' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ],
        ]);
    }
}
