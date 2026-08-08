<?php

namespace App\Http\Controllers\Api\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodItem;
use App\Services\Food\FoodSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodItemController extends Controller
{
    public function __construct(
        private readonly FoodSearchService $searchService,
    ) {}

    /**
     * GET /food/items — Search food items with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'              => 'nullable|string|max:255',
            'category'       => 'nullable|integer|exists:food_categories,id',
            'rating'         => 'nullable|numeric|min:0|max:5',
            'is_jain'        => 'nullable|boolean',
            'is_vegan'       => 'nullable|boolean',
            'spice_level'    => 'nullable|in:mild,medium,spicy,very_spicy',
            'provider_type'  => 'nullable|in:homemade,catering,hotel',
            'lat'            => 'required_with:lng|numeric|between:-90,90',
            'lng'            => 'required_with:lat|numeric|between:-180,180',
            'radius'         => 'nullable|integer|min:1|max:50',
            'sort'           => 'nullable|in:distance,rating,price,created_at',
            'direction'      => 'nullable|in:asc,desc',
            'per_page'       => 'nullable|integer|min:1|max:50',
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (int) ($validated['radius'] ?? 10);

        unset($validated['lat'], $validated['lng'], $validated['radius']);

        $items = $this->searchService->search(
            $validated['q'] ?? '',
            $validated,
            $lat,
            $lng,
            $radius
        );

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * GET /food/items/{slug} — Single food item detail.
     */
    public function show(string $slug): JsonResponse
    {
        $item = FoodItem::where('slug', $slug)
            ->where('is_available', true)
            ->with([
                'provider:id,business_name,logo_url,latitude,longitude,avg_rating,phone,provider_type,commission_rate',
                'category:id,name,slug',
                'pricingTiers',
                'reviews' => function ($q) {
                    $q->with('user:id,name,avatar')
                      ->latest()
                      ->limit(10);
                },
                'media',
            ])
            ->withCount(['reviews', 'orderItems'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * GET /food/items/featured — Featured food items.
     */
    public function featured(): JsonResponse
    {
        $items = $this->searchService->featured();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * GET /food/categories — List food categories.
     */
    public function categories(): JsonResponse
    {
        $categories = $this->searchService->getCategories();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }
}
