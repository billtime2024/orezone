<?php

namespace App\Http\Controllers\Api\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodProviderController extends Controller
{
    /**
     * GET /food/providers — List food providers with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_type' => 'nullable|in:homemade,catering,hotel',
            'cuisine_type'  => 'nullable|string|max:100',
            'min_rating'    => 'nullable|numeric|min:0|max:5',
            'search'        => 'nullable|string|max:255',
            'lat'           => 'nullable|numeric|between:-90,90',
            'lng'           => 'nullable|numeric|between:-180,180',
            'radius'        => 'nullable|integer|min:1|max:50',
            'sort'          => 'nullable|in:business_name,rating,created_at',
            'direction'     => 'nullable|in:asc,desc',
            'per_page'      => 'nullable|integer|min:1|max:50',
        ]);

        $query = FoodProvider::where('is_active', true)
            ->withCount('items');

        if (!empty($validated['provider_type'])) {
            $query->where('provider_type', $validated['provider_type']);
        }

        if (!empty($validated['cuisine_type'])) {
            $query->where('description', 'like', "%{$validated['cuisine_type']}%");
        }

        if (!empty($validated['min_rating'])) {
            $query->where('avg_rating', '>=', $validated['min_rating']);
        }

        if (!empty($validated['search'])) {
            $escaped = addcslashes($validated['search'], '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('business_name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                    ->orWhere('city', 'like', "%{$escaped}%");
            });
        }

        // Radius search
        if (!empty($validated['lat']) && !empty($validated['lng'])) {
            $radiusKm = (int) ($validated['radius'] ?? 10);
            $radiusMeters = $radiusKm * 1000;

            $query->selectRaw('food_providers.*')
                ->selectRaw('
                    (6371000 * acos(
                        cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))
                    )) AS distance_in_meters
                ', [$validated['lat'], $validated['lng'], $validated['lat']]);

            $query->havingRaw('distance_in_meters <= ?', [$radiusMeters]);
        }

        // Sort
        $sortBy = $validated['sort'] ?? 'created_at';
        $sortDir = $validated['direction'] ?? 'desc';

        if (!empty($validated['lat']) && $sortBy === 'rating') {
            $query->orderByRaw('distance_in_meters ASC');
        } else {
            $allowedSorts = ['business_name', 'rating', 'created_at'];
            $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = (int) ($validated['per_page'] ?? 20);
        $providers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $providers->getCollection(),
            'meta'    => [
                'current_page' => $providers->currentPage(),
                'last_page'    => $providers->lastPage(),
                'per_page'     => $providers->perPage(),
                'total'        => $providers->total(),
            ],
        ]);
    }

    /**
     * GET /food/providers/{id} — Provider profile with menu.
     */
    public function show(int $id): JsonResponse
    {
        $provider = FoodProvider::where('id', $id)
            ->where('is_active', true)
            ->with([
                'items' => function ($q) {
                    $q->where('is_available', true)
                      ->with(['pricingTiers', 'category:id,name'])
                      ->orderByDesc('created_at');
                },
                'reviews' => function ($q) {
                    $q->with('user:id,name,avatar')
                      ->latest()
                      ->limit(10);
                },
            ])
            ->withCount(['items', 'reviews'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $provider,
        ]);
    }
}
