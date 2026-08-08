<?php

namespace App\Services\Food;

use App\Models\Food\FoodCategory;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodProvider;
use Illuminate\Support\Collection;

class FoodSearchService
{
    /**
     * Search food items with filters and proximity.
     *
     * Supports filtering by: category, price_range, distance, rating,
     * dietary preferences (jain/vegan/gluten_free), spice_level, provider_type.
     * Uses Haversine formula for distance-based filtering and sorting.
     *
     * @param string $query Search text (matches item name, description)
     * @param array  $filters Filter parameters
     * @param float  $lat     User latitude
     * @param float  $lng     User longitude
     * @param int    $radiusKm Max radius in kilometres
     * @return \\Illuminate\\Contracts\\Pagination\\LengthAwarePaginator
     */
    public function search(
        string $query,
        array $filters,
        float $lat,
        float $lng,
        int $radiusKm = 10
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $radiusMeters = $radiusKm * 1000;

        $items = FoodItem::query()
            ->where('is_available', true)
            ->with(['provider:id,name,slug,logo_url,latitude,longitude,avg_rating,commission_rate,cuisine_type,provider_type'])
            ->selectRaw('food_items.*')
            ->selectRaw('
                (6371000 * acos(
                    cos(radians(?))
                    * cos(radians(food_items.latitude))
                    * cos(radians(food_items.longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(food_items.latitude))
                )) AS distance_in_meters
            ', [$lat, $lng, $lat]);

        // Text search
        if (!empty($query)) {
            $escapedQuery = addcslashes($query, '%_');
            $items->where(function ($q) use ($escapedQuery) {
                $q->where('name', 'like', "%{$escapedQuery}%")
                    ->orWhere('description', 'like', "%{$escapedQuery}%");
            });
        }

        // Distance filter (via Haversine)
        $items->havingRaw('distance_in_meters <= ?', [$radiusMeters]);

        // Category filter
        if (!empty($filters['category'])) {
            $items->where('category_id', $filters['category']);
        }

        // Price range filter: low / medium / high
        if (!empty($filters['price_range'])) {
            $items->where('price', '>=', $filters['price_range']['min'] ?? 0);
            if (!empty($filters['price_range']['max'])) {
                $items->where('price', '<=', $filters['price_range']['max']);
            }
        }

        // Minimum rating filter
        if (!empty($filters['rating'])) {
            $items->where('avg_rating', '>=', $filters['rating']);
        }

        // Dietary filters (boolean flags on the food item)
        if (!empty($filters['is_jain'])) {
            $items->where('is_jain', true);
        }
        if (!empty($filters['is_vegan'])) {
            $items->where('is_vegan', true);
        }
        if (!empty($filters['is_gluten_free'])) {
            $items->whereJsonDoesntContain('allergens', 'gluten');
        }

        // Spice level filter (exact match)
        if (!empty($filters['spice_level'])) {
            $items->where('spice_level', $filters['spice_level']);
        }

        // Provider type filter (restaurant / cloud_kitchen / home_chef / hotel)
        if (!empty($filters['provider_type'])) {
            $items->whereHas('provider', function ($q) use ($filters) {
                $q->where('provider_type', $filters['provider_type']);
            });
        }

        // Sort by proximity (nearest first) by default
        $sortBy = $filters['sort'] ?? 'distance';
        $sortDir = ($filters['direction'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $allowedSorts = ['distance', 'rating', 'price', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            switch ($sortBy) {
                case 'distance':
                    $items->orderByRaw('distance_in_meters ' . $sortDir);
                    break;
                case 'rating':
                    $items->orderBy('avg_rating', $sortDir);
                    break;
                case 'price':
                    $items->orderBy('price', $sortDir);
                    break;
                default:
                    $items->orderBy('created_at', $sortDir);
            }
        }

        $perPage = min((int) ($filters['per_page'] ?? 20), 50);

        return $items->paginate($perPage);
    }

    /**
     * Get featured food items.
     *
     * Returns items that are marked as featured and currently available,
     * sorted by rating descending.
     */
    public function featured(int $limit = 10): Collection
    {
        return FoodItem::where('is_featured', true)
            ->where('is_available', true)
            ->with(['provider:id,name,slug,logo_url,avg_rating'])
            ->orderByDesc('avg_rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Get food categories.
     */
    public function getCategories(): Collection
    {
        return FoodCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
