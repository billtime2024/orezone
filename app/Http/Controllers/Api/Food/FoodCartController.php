<?php

namespace App\Http\Controllers\Api\Food;

use App\Http\Controllers\Controller;
use App\Services\Food\FoodCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodCartController extends Controller
{
    public function __construct(
        private readonly FoodCartService $cartService,
    ) {}

    /**
     * GET /food/cart — Get user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $cartItems = $this->cartService->getCart($userId);
        $totals = $this->cartService->getCartTotal($userId);
        $validation = $this->cartService->validateCart($userId);

        return response()->json([
            'success' => true,
            'data'    => [
                'items'       => $cartItems,
                'totals'      => $totals,
                'unavailable' => $validation['unavailable'],
            ],
        ]);
    }

    /**
     * POST /food/cart — Add item to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'food_item_id'      => 'required|integer|exists:food_items,id',
            'pricing_tier_id'   => 'nullable|integer|exists:food_pricing_tiers,id',
            'quantity'          => 'nullable|integer|min:1|max:20',
            'notes'             => 'nullable|string|max:500',
        ]);

        $userId = $request->user()->id;

        $cartItem = $this->cartService->addToCart(
            $userId,
            $validated['food_item_id'],
            $validated['pricing_tier_id'] ?? null,
            $validated['quantity'] ?? 1,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'data'    => $cartItem,
        ], 201);
    }

    /**
     * PUT /food/cart/{id} — Update cart item quantity.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        $cartItem = $this->cartService->updateCartItem($id, $validated['quantity'], $request->user()->id);

        return response()->json([
            'success' => true,
            'data'    => $cartItem,
        ]);
    }

    /**
     * DELETE /food/cart/{id} — Remove item from cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $removed = $this->cartService->removeFromCart($id, $request->user()->id);

        if (!$removed) {
            return response()->json([
                'success' => false,
                'error'   => 'Cart item not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }
}
