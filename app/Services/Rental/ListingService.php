<?php

namespace App\Services\Rental;

use App\Models\RentalListing;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ListingService
{
    /**
     * Create a new rental listing with type-specific details.
     */
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

        return $listing->load(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails']);
    }

    /**
     * Update a listing and its type-specific details.
     */
    public function updateListing(RentalListing $listing, array $data, ?array $typeDetails = null): RentalListing
    {
        $listing->update($data);

        if ($typeDetails && $listing->details) {
            $listing->details->update($typeDetails);
        }

        return $listing->fresh(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails']);
    }

    /**
     * Upload photos for a listing.
     */
    public function uploadPhotos(RentalListing $listing, array $files): array
    {
        $photos = $listing->photos ?? [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('rental-listings/' . $listing->id, 'public');
                $photos[] = '/storage/' . $path;
            }
        }

        $listing->update(['photos' => $photos]);
        return $photos;
    }

    /**
     * Delete a photo from a listing.
     */
    public function deletePhoto(RentalListing $listing, string $photoUrl): bool
    {
        $photos = $listing->photos ?? [];
        $photos = array_values(array_filter($photos, fn ($p) => $p !== $photoUrl));
        $listing->update(['photos' => $photos]);

        // Delete physical file from storage
        $this->deletePhotoFile($photoUrl);

        return true;
    }

    /**
     * Delete a photo file from disk.
     */
    private function deletePhotoFile(string $photoUrl): void
    {
        // Convert URL path to storage path
        // URLs are like: /storage/rental-listings/1/photo.jpg or /storage/rental-listings/house1-1.jpg
        $path = str_replace('/storage/', '', $photoUrl);

        $fullPath = storage_path('app/public/' . $path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Also check public directory for direct copies
        $publicPath = public_path('storage/' . $path);
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }
    }

    /**
     * Search listings with filters.
     */
    public function search(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = RentalListing::query()
            ->with(['houseDetails', 'carDetails', 'commercialDetails', 'roomDetails'])
            ->withAvg('reviews', 'rating')
            ->withCount('bookings');

        // Type filter
        if (!empty($filters['rental_type'])) {
            $query->ofType($filters['rental_type']);
        }

        // City filter
        if (!empty($filters['city'])) {
            $query->inCity($filters['city']);
        }

        // Price range
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $min = $filters['min_price'] ?? 0;
            $max = $filters['max_price'] ?? 999999;
            $query->priceBetween($min, $max);
        }

        // Date availability filter
        if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
            $listingIds = $this->getAvailableListingIds(
                $filters['check_in'],
                $filters['check_out'],
                $filters['rental_type'] ?? null
            );
            $query->whereIn('id', $listingIds);
        }

        // Text search
        if (!empty($filters['search'])) {
            $search = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['direction'] ?? 'desc';
        $allowedSorts = ['price_per_unit', 'created_at', 'reviews_avg_rating', 'total_bookings'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query->active()->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get a single listing with all relations.
     */
    public function getListing(RentalListing $listing): RentalListing
    {
        return $listing->load([
            'houseDetails', 'carDetails', 'commercialDetails', 'roomDetails',
            'owner:id,name,email,phone',
            'reviews' => function ($q) {
                $q->with('user:id,name')
                  ->where('is_visible', true)
                  ->latest()
                  ->limit(10);
            },
        ]);
    }

    /**
     * Get available listing IDs for given dates.
     */
    private function getAvailableListingIds(string $checkIn, string $checkOut, ?string $type): array
    {
        $query = RentalListing::active()
            ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', ['pending', 'confirmed', 'active'])
                  ->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
            });

        if ($type) {
            $query->ofType($type);
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Delete a listing (soft delete).
     */
    public function deleteListing(RentalListing $listing): bool
    {
        // Check for active bookings
        $hasActive = $listing->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->exists();

        if ($hasActive) {
            return false;
        }

        // Delete all photos from disk before deleting listing
        $photos = $listing->photos ?? [];
        foreach ($photos as $photoUrl) {
            $this->deletePhotoFile($photoUrl);
        }

        // Also delete the entire listing photos directory
        $listingDir = storage_path('app/public/rental-listings/' . $listing->id);
        if (is_dir($listingDir)) {
            array_map('unlink', glob("$listingDir/*"));
            rmdir($listingDir);
        }
        $publicDir = public_path('storage/rental-listings/' . $listing->id);
        if (is_dir($publicDir)) {
            array_map('unlink', glob("$publicDir/*"));
            rmdir($publicDir);
        }

        $listing->delete();
        return true;
    }

    /**
     * Toggle listing status between active and paused.
     */
    public function toggleStatus(RentalListing $listing): RentalListing
    {
        $newStatus = $listing->status === 'active' ? 'paused' : 'active';
        $listing->update(['status' => $newStatus]);
        return $listing;
    }
}
