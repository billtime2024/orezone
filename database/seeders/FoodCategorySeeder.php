<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Comprehensive food categories for PURE VEG community food services.
     * Categories are hierarchical: parent categories first, then sub-categories.
     */
    public function run(): void
    {
        // Parent categories with their sub-categories
        $categories = [
            // 1. Home Cooked
            [
                'name' => 'Home Cooked',
                'slug' => 'home-cooked',
                'icon' => 'home',
                'sort_order' => 1,
                'subcategories' => [
                    ['name' => 'Daily Tiffin', 'slug' => 'daily-tiffin', 'icon' => 'tiffin', 'sort_order' => 1],
                    ['name' => 'Home Specialties', 'slug' => 'home-specialties', 'icon' => 'star', 'sort_order' => 2],
                    ['name' => "Grandma's Recipes", 'slug' => 'grandmas-recipes', 'icon' => 'elderly', 'sort_order' => 3],
                ],
            ],

            // 2. South Indian
            [
                'name' => 'South Indian',
                'slug' => 'south-indian',
                'icon' => 'rice-bowl',
                'sort_order' => 2,
                'subcategories' => [
                    ['name' => 'Dosa & Varieties', 'slug' => 'dosa-varieties', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Idli & Vada', 'slug' => 'idli-vada', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Uttapam', 'slug' => 'uttapam', 'icon' => 'food', 'sort_order' => 3],
                    ['name' => 'Meals & Thali', 'slug' => 'south-indian-meals-thali', 'icon' => 'thali', 'sort_order' => 4],
                    ['name' => 'Filter Coffee & Beverages', 'slug' => 'filter-coffee-beverages', 'icon' => 'coffee', 'sort_order' => 5],
                ],
            ],

            // 3. North Indian
            [
                'name' => 'North Indian',
                'slug' => 'north-indian',
                'icon' => 'curry',
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Paratha & Roti', 'slug' => 'paratha-roti', 'icon' => 'flatbread', 'sort_order' => 1],
                    ['name' => 'Curry & Gravy', 'slug' => 'curry-gravy', 'icon' => 'curry', 'sort_order' => 2],
                    ['name' => 'Chole Bhature', 'slug' => 'chole-bhature', 'icon' => 'food', 'sort_order' => 3],
                    ['name' => 'Rajma Chawal', 'slug' => 'rajma-chawal', 'icon' => 'rice', 'sort_order' => 4],
                    ['name' => 'Paneer Specialties', 'slug' => 'paneer-specialties', 'icon' => 'cheese', 'sort_order' => 5],
                ],
            ],

            // 4. Gujarati
            [
                'name' => 'Gujarati',
                'slug' => 'gujarati',
                'icon' => 'thali',
                'sort_order' => 4,
                'subcategories' => [
                    ['name' => 'Dhokla & Fafda', 'slug' => 'dhokla-fafda', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Thepla & Khandvi', 'slug' => 'thepla-khandvi', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Gujarati Thali', 'slug' => 'gujarati-thali', 'icon' => 'thali', 'sort_order' => 3],
                    ['name' => 'Farsan', 'slug' => 'farsan', 'icon' => 'food', 'sort_order' => 4],
                ],
            ],

            // 5. Rajasthani
            [
                'name' => 'Rajasthani',
                'slug' => 'rajasthani',
                'icon' => 'food',
                'sort_order' => 5,
                'subcategories' => [
                    ['name' => 'Dal Baati Churma', 'slug' => 'dal-baati-churma', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Gatte Ki Sabzi', 'slug' => 'gatte-ki-sabzi', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Rajasthani Thali', 'slug' => 'rajasthani-thali', 'icon' => 'thali', 'sort_order' => 3],
                ],
            ],

            // 6. Chinese (Veg)
            [
                'name' => 'Chinese (Veg)',
                'slug' => 'chinese-veg',
                'icon' => 'noodles',
                'sort_order' => 6,
                'subcategories' => [
                    ['name' => 'Noodles & Chow Mein', 'slug' => 'noodles-chow-mein', 'icon' => 'noodles', 'sort_order' => 1],
                    ['name' => 'Manchurian', 'slug' => 'manchurian', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Fried Rice', 'slug' => 'fried-rice', 'icon' => 'rice', 'sort_order' => 3],
                    ['name' => 'Starters', 'slug' => 'chinese-starters', 'icon' => 'food', 'sort_order' => 4],
                ],
            ],

            // 7. Street Food (Veg)
            [
                'name' => 'Street Food (Veg)',
                'slug' => 'street-food-veg',
                'icon' => 'street-food',
                'sort_order' => 7,
                'subcategories' => [
                    ['name' => 'Pani Puri & Chaat', 'slug' => 'pani-puri-chaat', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Pav Bhaji', 'slug' => 'pav-bhaji', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Vada Pav', 'slug' => 'vada-pav', 'icon' => 'food', 'sort_order' => 3],
                    ['name' => 'Samosa & Kachori', 'slug' => 'samosa-kachori', 'icon' => 'food', 'sort_order' => 4],
                    ['name' => 'Bhel Puri', 'slug' => 'bhel-puri', 'icon' => 'food', 'sort_order' => 5],
                ],
            ],

            // 8. Desserts
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'icon' => 'cake',
                'sort_order' => 8,
                'subcategories' => [
                    ['name' => 'Indian Mithai', 'slug' => 'indian-mithai', 'icon' => 'candy', 'sort_order' => 1],
                    ['name' => 'Halwa & Kheer', 'slug' => 'halwa-kheer', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Ice Cream & Kulfi', 'slug' => 'ice-cream-kulfi', 'icon' => 'ice-cream', 'sort_order' => 3],
                    ['name' => 'Cakes & Pastries', 'slug' => 'cakes-pastries', 'icon' => 'cake', 'sort_order' => 4],
                ],
            ],

            // 9. Beverages
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'icon' => 'drink',
                'sort_order' => 9,
                'subcategories' => [
                    ['name' => 'Lassi & Buttermilk', 'slug' => 'lassi-buttermilk', 'icon' => 'drink', 'sort_order' => 1],
                    ['name' => 'Juices & Shakes', 'slug' => 'juices-shakes', 'icon' => 'juice', 'sort_order' => 2],
                    ['name' => 'Masala Chai', 'slug' => 'masala-chai', 'icon' => 'tea', 'sort_order' => 3],
                    ['name' => 'Cold Drinks', 'slug' => 'cold-drinks', 'icon' => 'drink', 'sort_order' => 4],
                ],
            ],

            // 10. Snacks & Chaat
            [
                'name' => 'Snacks & Chaat',
                'slug' => 'snacks-chaat',
                'icon' => 'snack',
                'sort_order' => 10,
                'subcategories' => [
                    ['name' => 'Namkeen & Mixture', 'slug' => 'namkeen-mixture', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Pakoda & Bhajia', 'slug' => 'pakoda-bhajia', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Sandwich & Burger', 'slug' => 'sandwich-burger', 'icon' => 'sandwich', 'sort_order' => 3],
                ],
            ],

            // 11. South Indian Tiffin
            [
                'name' => 'South Indian Tiffin',
                'slug' => 'south-indian-tiffin',
                'icon' => 'tiffin',
                'sort_order' => 11,
                'subcategories' => [
                    ['name' => 'Mini Meals', 'slug' => 'mini-meals', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Combo Packs', 'slug' => 'combo-packs', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Family Packs', 'slug' => 'family-packs', 'icon' => 'family', 'sort_order' => 3],
                ],
            ],

            // 12. Festival Special
            [
                'name' => 'Festival Special',
                'slug' => 'festival-special',
                'icon' => 'festival',
                'sort_order' => 12,
                'subcategories' => [
                    ['name' => 'Diwali Sweets', 'slug' => 'diwali-sweets', 'icon' => 'candy', 'sort_order' => 1],
                    ['name' => 'Navratri Special', 'slug' => 'navratri-special', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Ganesh Chaturthi', 'slug' => 'ganesh-chaturthi', 'icon' => 'food', 'sort_order' => 3],
                    ['name' => 'Holi Special', 'slug' => 'holi-special', 'icon' => 'food', 'sort_order' => 4],
                ],
            ],

            // 13. Wedding Catering
            [
                'name' => 'Wedding Catering',
                'slug' => 'wedding-catering',
                'icon' => 'wedding',
                'sort_order' => 13,
                'subcategories' => [
                    ['name' => 'Veg Thali Package', 'slug' => 'wedding-veg-thali-package', 'icon' => 'thali', 'sort_order' => 1],
                    ['name' => 'Live Counter', 'slug' => 'wedding-live-counter', 'icon' => 'food', 'sort_order' => 2],
                    ['name' => 'Welcome Drinks', 'slug' => 'wedding-welcome-drinks', 'icon' => 'drink', 'sort_order' => 3],
                ],
            ],

            // 14. Corporate Catering
            [
                'name' => 'Corporate Catering',
                'slug' => 'corporate-catering',
                'icon' => 'business',
                'sort_order' => 14,
                'subcategories' => [
                    ['name' => 'Lunch Box', 'slug' => 'corporate-lunch-box', 'icon' => 'lunchbox', 'sort_order' => 1],
                    ['name' => 'Tea Snacks', 'slug' => 'corporate-tea-snacks', 'icon' => 'snack', 'sort_order' => 2],
                    ['name' => 'Full Day Package', 'slug' => 'corporate-full-day-package', 'icon' => 'food', 'sort_order' => 3],
                ],
            ],

            // 15. Party Package
            [
                'name' => 'Party Package',
                'slug' => 'party-package',
                'icon' => 'party',
                'sort_order' => 15,
                'subcategories' => [
                    ['name' => 'Birthday Party', 'slug' => 'birthday-party', 'icon' => 'cake', 'sort_order' => 1],
                    ['name' => 'Anniversary', 'slug' => 'anniversary', 'icon' => 'celebration', 'sort_order' => 2],
                    ['name' => 'Kitty Party', 'slug' => 'kitty-party', 'icon' => 'party', 'sort_order' => 3],
                ],
            ],

            // 16. Buffet
            [
                'name' => 'Buffet',
                'slug' => 'buffet',
                'icon' => 'buffet',
                'sort_order' => 16,
                'subcategories' => [
                    ['name' => 'Veg Buffet', 'slug' => 'veg-buffet', 'icon' => 'food', 'sort_order' => 1],
                    ['name' => 'Jain Buffet', 'slug' => 'jain-buffet', 'icon' => 'food', 'sort_order' => 2],
                ],
            ],

            // 17. Room Service
            [
                'name' => 'Room Service',
                'slug' => 'room-service',
                'icon' => 'room-service',
                'sort_order' => 17,
                'subcategories' => [
                    ['name' => 'Breakfast', 'slug' => 'room-service-breakfast', 'icon' => 'breakfast', 'sort_order' => 1],
                    ['name' => 'Lunch', 'slug' => 'room-service-lunch', 'icon' => 'lunch', 'sort_order' => 2],
                    ['name' => 'Dinner', 'slug' => 'room-service-dinner', 'icon' => 'dinner', 'sort_order' => 3],
                    ['name' => 'Late Night', 'slug' => 'room-service-late-night', 'icon' => 'moon', 'sort_order' => 4],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $parentId = DB::table('food_categories')->insertGetId([
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'icon' => $categoryData['icon'],
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($categoryData['subcategories'] as $subcategory) {
                DB::table('food_categories')->insert([
                    'parent_id' => $parentId,
                    'name' => $subcategory['name'],
                    'slug' => $subcategory['slug'],
                    'icon' => $subcategory['icon'],
                    'sort_order' => $subcategory['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}