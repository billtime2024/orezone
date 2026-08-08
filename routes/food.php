<?php

use App\Http\Controllers\Api\Food\CateringController;
use App\Http\Controllers\Api\Food\FoodCartController;
use App\Http\Controllers\Api\Food\FoodItemController;
use App\Http\Controllers\Api\Food\FoodOrderController;
use App\Http\Controllers\Api\Food\FoodProviderController;
use App\Http\Controllers\Api\Food\Provider\ProviderDashboardController;
use App\Http\Controllers\Api\Food\Provider\ProviderMenuController;
use App\Http\Controllers\Api\Food\Provider\ProviderOrderController;
use App\Http\Controllers\Api\Food\Provider\ProviderRegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Food Services API Routes
|--------------------------------------------------------------------------
|
| Pure veg food ordering, catering, hotel dining, and provider management.
| Routes are grouped by access level: public, authenticated, provider.
|
*/

Route::prefix('v1')->group(function () {

    // ── Public Routes ──────────────────────────────────────────────
    // No authentication required — discover food and providers
    Route::prefix('food')->group(function () {

        // Categories
        Route::get('categories', [FoodItemController::class, 'categories'])
            ->name('food.categories');

        // Food items — search, detail, featured
        Route::get('items', [FoodItemController::class, 'index'])
            ->name('food.items.index');
        Route::get('items/featured', [FoodItemController::class, 'featured'])
            ->name('food.items.featured');
        Route::get('items/{slug}', [FoodItemController::class, 'show'])
            ->name('food.items.show');

        // Providers — list, profile with menu
        Route::get('providers', [FoodProviderController::class, 'index'])
            ->name('food.providers.index');
        Route::get('providers/{slug}', [FoodProviderController::class, 'show'])
            ->name('food.providers.show');
    });

    // ── Authenticated User Routes ──────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // ── Cart ───────────────────────────────────────────────────
        Route::prefix('food/cart')->group(function () {
            Route::get('/', [FoodCartController::class, 'index'])
                ->name('food.cart.index');
            Route::post('/', [FoodCartController::class, 'store'])
                ->name('food.cart.store');
            Route::put('{id}', [FoodCartController::class, 'update'])
                ->name('food.cart.update');
            Route::delete('{id}', [FoodCartController::class, 'destroy'])
                ->name('food.cart.destroy');
        });

        // ── Orders ─────────────────────────────────────────────────
        Route::prefix('food/orders')->group(function () {
            Route::get('/', [FoodOrderController::class, 'index'])
                ->name('food.orders.index');
            Route::post('/', [FoodOrderController::class, 'store'])
                ->name('food.orders.store');
            Route::get('{id}', [FoodOrderController::class, 'show'])
                ->name('food.orders.show');
            Route::post('{id}/cancel', [FoodOrderController::class, 'cancel'])
                ->name('food.orders.cancel');
            Route::post('{id}/rate', [FoodOrderController::class, 'rate'])
                ->name('food.orders.rate');
        });

        // ── Catering ───────────────────────────────────────────────
        Route::prefix('food/catering')->group(function () {
            Route::get('/', [CateringController::class, 'index'])
                ->name('food.catering.index');
            Route::post('/', [CateringController::class, 'store'])
                ->name('food.catering.store');
            Route::get('{id}', [CateringController::class, 'show'])
                ->name('food.catering.show');
            Route::post('{id}/select-quote/{quoteId}', [CateringController::class, 'selectQuote'])
                ->name('food.catering.select-quote');
            Route::post('{id}/cancel', [CateringController::class, 'cancel'])
                ->name('food.catering.cancel');
        });
    });

    // ── Provider Routes ────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->prefix('food/provider')->group(function () {

        // Dashboard
        Route::get('dashboard', [ProviderDashboardController::class, 'index'])
            ->name('food.provider.dashboard');

        // Registration & Profile
        Route::post('register', [ProviderRegistrationController::class, 'register'])
            ->name('food.provider.register');
        Route::get('profile', [ProviderRegistrationController::class, 'getProfile'])
            ->name('food.provider.profile');
        Route::put('profile', [ProviderRegistrationController::class, 'updateProfile'])
            ->name('food.provider.profile.update');
        Route::post('documents', [ProviderRegistrationController::class, 'uploadDocuments'])
            ->name('food.provider.documents');

        // Menu management
        Route::get('menu', [ProviderMenuController::class, 'index'])
            ->name('food.provider.menu.index');
        Route::post('menu', [ProviderMenuController::class, 'store'])
            ->name('food.provider.menu.store');
        Route::put('menu/{id}', [ProviderMenuController::class, 'update'])
            ->name('food.provider.menu.update');
        Route::delete('menu/{id}', [ProviderMenuController::class, 'destroy'])
            ->name('food.provider.menu.destroy');
        Route::post('menu/{id}/toggle', [ProviderMenuController::class, 'toggleAvailability'])
            ->name('food.provider.menu.toggle');

        // Order management
        Route::get('orders', [ProviderOrderController::class, 'index'])
            ->name('food.provider.orders.index');
        Route::get('orders/{id}', [ProviderOrderController::class, 'show'])
            ->name('food.provider.orders.show');
        Route::put('orders/{id}/status', [ProviderOrderController::class, 'updateStatus'])
            ->name('food.provider.orders.update-status');
    });
});
