<?php

namespace App\Http\Controllers\Api\Food\Provider;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodPricingTier;
use App\Models\Food\FoodProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderMenuController extends Controller
{
    /**
     * GET /food/provider/menu — List provider's menu items.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $this->getProvider($request);

        $items = FoodItem::where('provider_id', $provider->id)
            ->with(['pricingTiers', 'category:id,name'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $items->getCollection(),
            'meta'    => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    /**
     * POST /food/provider/menu — Create a food item.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string|max:2000',
            'category_id'         => 'required|integer|exists:food_categories,id',
            'price'               => 'required|numeric|min:0',
            'unit'                => 'required|in:plate,bowl,kg,ltr,dozen,parcel',
            'image_url'           => 'nullable|string|max:500',
            'is_available'        => 'nullable|boolean',
            'is_featured'         => 'nullable|boolean',
            'is_jain'             => 'nullable|boolean',
            'is_vegan'            => 'nullable|boolean',
            'spice_level'         => 'nullable|in:mild,medium,spicy,very_spicy',
            'preparation_time_min' => 'required|integer|min:1',
            'ingredients'         => 'nullable|string|max:2000',
            'allergens'           => 'nullable|string',
            'min_quantity'        => 'nullable|integer|min:1',
            'max_quantity'        => 'nullable|integer|min:1',
            'pricing_tiers'       => 'nullable|array',
            'pricing_tiers.*.name'      => 'required_with:pricing_tiers|string|max:100',
            'pricing_tiers.*.price'     => 'required_with:pricing_tiers|numeric|min:0',
            'pricing_tiers.*.unit'      => 'nullable|string|max:50',
            'pricing_tiers.*.min_qty'   => 'nullable|integer|min:1',
        ]);

        $provider = $this->getProvider($request);

        // Generate slug
        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $existingCount = FoodItem::where('slug', $slug)->count();
        if ($existingCount > 0) {
            $slug = $slug . '-' . ($existingCount + 1);
        }

        $itemData = collect($validated)->except(['pricing_tiers'])->toArray();
        $itemData['provider_id'] = $provider->id;
        $itemData['slug'] = $slug;

        // Map image_url from validation
        if (isset($itemData['image_url'])) {
            $itemData['image_url'] = $itemData['image_url'];
        }

        $item = FoodItem::create($itemData);

        // Create pricing tiers
        if (!empty($validated['pricing_tiers'])) {
            foreach ($validated['pricing_tiers'] as $tier) {
                FoodPricingTier::create(array_merge($tier, [
                    'food_item_id' => $item->id,
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $item->fresh(['pricingTiers', 'category']),
        ], 201);
    }

    /**
     * PUT /food/provider/menu/{id} — Update a food item.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'description'         => 'nullable|string|max:2000',
            'category_id'         => 'sometimes|integer|exists:food_categories,id',
            'price'               => 'sometimes|numeric|min:0',
            'unit'                => 'sometimes|in:plate,bowl,kg,ltr,dozen,parcel',
            'image_url'           => 'nullable|string|max:500',
            'is_available'        => 'sometimes|boolean',
            'is_featured'         => 'sometimes|boolean',
            'is_jain'             => 'sometimes|boolean',
            'is_vegan'            => 'sometimes|boolean',
            'spice_level'         => 'sometimes|in:mild,medium,spicy,very_spicy',
            'preparation_time_min' => 'sometimes|integer|min:1',
            'ingredients'         => 'nullable|string|max:2000',
            'allergens'           => 'nullable|string',
            'min_quantity'        => 'nullable|integer|min:1',
            'max_quantity'        => 'nullable|integer|min:1',
            'pricing_tiers'       => 'nullable|array',
            'pricing_tiers.*.id'          => 'nullable|integer',
            'pricing_tiers.*.name'        => 'required_with:pricing_tiers|string|max:100',
            'pricing_tiers.*.price'       => 'required_with:pricing_tiers|numeric|min:0',
            'pricing_tiers.*.unit'        => 'nullable|string|max:50',
            'pricing_tiers.*.min_qty'     => 'nullable|integer|min:1',
            'pricing_tiers.*._delete'     => 'nullable|boolean',
        ]);

        $provider = $this->getProvider($request);

        $item = FoodItem::where('id', $id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        $itemData = collect($validated)->except(['pricing_tiers'])->toArray();

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $item->name) {
            $slug = \Illuminate\Support\Str::slug($validated['name']);
            $existingCount = FoodItem::where('slug', $slug)
                ->where('id', '!=', $item->id)
                ->count();
            if ($existingCount > 0) {
                $slug = $slug . '-' . ($existingCount + 1);
            }
            $itemData['slug'] = $slug;
        }

        $item->update($itemData);

        // Handle pricing tiers
        if (isset($validated['pricing_tiers'])) {
            foreach ($validated['pricing_tiers'] as $tierData) {
                // Delete marked tiers
                if (!empty($tierData['_delete']) && !empty($tierData['id'])) {
                    FoodPricingTier::where('id', $tierData['id'])
                        ->where('food_item_id', $item->id)
                        ->delete();
                    continue;
                }

                // Update existing tier
                if (!empty($tierData['id'])) {
                    FoodPricingTier::where('id', $tierData['id'])
                        ->where('food_item_id', $item->id)
                        ->update(collect($tierData)->except(['_delete'])->toArray());
                    continue;
                }

                // Create new tier
                FoodPricingTier::create(array_merge(
                    collect($tierData)->except(['_delete'])->toArray(),
                    ['food_item_id' => $item->id]
                ));
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $item->fresh(['pricingTiers', 'category']),
        ]);
    }

    /**
     * DELETE /food/provider/menu/{id} — Delete a food item.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $provider = $this->getProvider($request);

        $item = FoodItem::where('id', $id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        // Soft delete: mark as unavailable instead of hard delete
        $item->update([
            'is_available' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    /**
     * POST /food/provider/menu/{id}/toggle — Toggle item availability.
     */
    public function toggleAvailability(Request $request, int $id): JsonResponse
    {
        $provider = $this->getProvider($request);

        $item = FoodItem::where('id', $id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        $item->update([
            'is_available' => !$item->is_available,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $item->id,
                'is_available' => $item->is_available,
            ],
        ]);
    }

    /**
     * Get the provider record for the authenticated user.
     */
    private function getProvider(Request $request): FoodProvider
    {
        $provider = FoodProvider::where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$provider->is_active || $provider->verification_status !== 'approved') {
            abort(403, 'Your provider account is not active or approved.');
        }

        return $provider;
    }
}
