<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodAllergenSeeder extends Seeder
{
    /**
     * Common food allergens for the PURE VEG community food services.
     * These are standard allergens that should be tracked for food items.
     *
     * Note: In a pure veg community, eggs are still relevant as some
     * vegetarian items may contain eggs (ovo-vegetarian).
     */
    public function run(): void
    {
        $allergens = [
            [
                'name' => 'Gluten',
                'slug' => 'gluten',
                'description' => 'Wheat, barley, rye, and their derivatives',
                'icon' => 'wheat',
                'severity' => 'high',
                'common_in' => 'Bread, roti, pasta, noodles, baked goods',
            ],
            [
                'name' => 'Dairy',
                'slug' => 'dairy',
                'description' => 'Milk and milk products (lactose)',
                'icon' => 'milk',
                'severity' => 'medium',
                'common_in' => 'Paneer, butter, ghee, lassi, ice cream, cheese',
            ],
            [
                'name' => 'Nuts',
                'slug' => 'nuts',
                'description' => 'Tree nuts (almonds, cashews, walnuts, pistachios)',
                'icon' => 'nut',
                'severity' => 'high',
                'common_in' => 'Sweets, desserts, curries, korma',
            ],
            [
                'name' => 'Soy',
                'slug' => 'soy',
                'description' => 'Soybeans and soy products',
                'icon' => 'soybean',
                'severity' => 'medium',
                'common_in' => 'Soy sauce, tofu, edamame, processed foods',
            ],
            [
                'name' => 'Eggs',
                'slug' => 'eggs',
                'description' => 'Chicken eggs and egg products',
                'icon' => 'egg',
                'severity' => 'high',
                'common_in' => 'Cakes, pastries, mayonnaise, some snacks',
            ],
            [
                'name' => 'Peanuts',
                'slug' => 'peanuts',
                'description' => 'Peanuts and peanut products',
                'icon' => 'peanut',
                'severity' => 'high',
                'common_in' => 'Chikki, ladoo, snacks, chutneys',
            ],
            [
                'name' => 'Sesame',
                'slug' => 'sesame',
                'description' => 'Sesame seeds and sesame oil',
                'icon' => 'seed',
                'severity' => 'medium',
                'common_in' => 'Til ladoo, chutneys, bread toppings',
            ],
            [
                'name' => 'Mustard',
                'slug' => 'mustard',
                'description' => 'Mustard seeds and mustard paste',
                'icon' => 'mustard',
                'severity' => 'low',
                'common_in' => 'Pickles, curries, tempering',
            ],
            [
                'name' => 'Celery',
                'slug' => 'celery',
                'description' => 'Celery and celery products',
                'icon' => 'celery',
                'severity' => 'low',
                'common_in' => 'Soups, salads, some curries',
            ],
            [
                'name' => 'Lupin',
                'slug' => 'lupin',
                'description' => 'Lupin beans and lupin flour',
                'icon' => 'bean',
                'severity' => 'medium',
                'common_in' => 'Some baked goods, flour blends',
            ],
        ];

        foreach ($allergens as $allergen) {
            DB::table('food_allergens')->updateOrInsert(
                ['slug' => $allergen['slug']],
                [
                    'name' => $allergen['name'],
                    'description' => $allergen['description'],
                    'icon' => $allergen['icon'],
                    'severity' => $allergen['severity'],
                    'common_in' => $allergen['common_in'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}