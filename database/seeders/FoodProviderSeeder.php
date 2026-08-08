<?php

namespace Database\Seeders;

use App\Models\Food\FoodCategory;
use App\Models\Food\FoodDeliverySlot;
use App\Models\Food\FoodItem;
use App\Models\Food\FoodPricingTier;
use App\Models\Food\FoodProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodProviderSeeder extends Seeder
{
    // Meivazhisalai, Pudukkottai district coordinates
    private float $baseLat = 10.2527;
    private float $baseLng = 78.6542;

    public function run(): void
    {
        // Clear existing data (order matters for foreign keys)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        FoodDeliverySlot::truncate();
        FoodPricingTier::truncate();
        FoodItem::truncate();
        FoodProvider::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = FoodCategory::pluck('id', 'slug')->toArray();

        // ── Provider 1: Amma's Kitchen (Homemade - Tamil Meals) ─────
        $amma = $this->createProvider(
            userId: $this->getUserId('9000000003'),
            type: 'homemade',
            name: "Amma's Kitchen",
            desc: 'Genuine Pudukkottai style home meals. Traditional Tamil sambar, rasam, kootu, poriyal. Pure veg, no onion no garlic options available.',
            address: 'Meivazhisalai, Pudukkottai',
            rating: 4.8,
            orders: 342
        );
        $this->createMenu($amma, $categories, [
            ['name' => 'Sambar Rice', 'cat' => 'south-indian-meals-thali', 'price' => 60, 'desc' => 'Traditional Tamil sambar rice with vegetables, served with pickle and papad', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Curd Rice', 'cat' => 'south-indian-meals-thali', 'price' => 50, 'desc' => 'Creamy curd rice with tempered mustard seeds and curry leaves', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Lemon Rice', 'cat' => 'south-indian-meals-thali', 'price' => 55, 'desc' => 'Tangy elumichai sadam with peanuts and chana dal', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Tomato Rice', 'cat' => 'south-indian-meals-thali', 'price' => 55, 'desc' => 'Spicy thakkali sadam with fresh tomatoes', 'jain' => true, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Pongal', 'cat' => 'south-indian-tiffin', 'price' => 45, 'desc' => 'Ven pongal with ghee, pepper and cashews', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Rasam Rice', 'cat' => 'south-indian-meals-thali', 'price' => 50, 'desc' => 'Milagu rasam mixed with rice, comfort food', 'jain' => true, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Full Meals', 'cat' => 'south-indian-meals-thali', 'price' => 90, 'desc' => 'Complete Tamil meals - rice, sambar, rasam, kootu, poriyal, curd, pickle, papad, payasam', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Filter Coffee', 'cat' => 'south-indian-filter-coffee-beverages', 'price' => 20, 'desc' => 'Strong decoction filter coffee with fresh milk', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Kootu Rice', 'cat' => 'south-indian-meals-thali', 'price' => 55, 'desc' => 'Moong dal kootu mixed with rice', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Poriyal Rice', 'cat' => 'south-indian-meals-thali', 'price' => 55, 'desc' => 'Beans poriyal with rice and sambar', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
        ]);

        // ── Provider 2: Meivazhisalai Tiffin Centre ─────────────────
        $tiffin = $this->createProvider(
            userId: $this->getUserId('9000000004'),
            type: 'homemade',
            name: 'Meivazhisalai Tiffin Centre',
            desc: 'Fresh breakfast tiffins every morning. Crispy dosas, soft idlis, vadai. Family run since 1995.',
            address: 'Meivazhisalai Bus Stand, Pudukkottai',
            rating: 4.6,
            orders: 218
        );
        $this->createMenu($tiffin, $categories, [
            ['name' => 'Masala Dosa', 'cat' => 'south-indian-dosa-varieties', 'price' => 60, 'desc' => 'Crispy dosa with potato masala filling', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Rava Dosa', 'cat' => 'south-indian-dosa-varieties', 'price' => 55, 'desc' => 'Crispy semolina dosa with onions', 'jain' => false, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Plain Dosa', 'cat' => 'south-indian-dosa-varieties', 'price' => 40, 'desc' => 'Simple crispy dosa with chutney and sambar', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Idli (4 pcs)', 'cat' => 'south-indian-idli-vada', 'price' => 35, 'desc' => 'Soft steamed idlis with chutney and sambar', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Medu Vada (2 pcs)', 'cat' => 'south-indian-idli-vada', 'price' => 40, 'desc' => 'Crispy urad dal vadas with chutney', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Uttapam', 'cat' => 'south-indian-uttapam', 'price' => 50, 'desc' => 'Thick pancake with onions and tomatoes', 'jain' => false, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Pongal Vada Combo', 'cat' => 'south-indian-tiffin', 'price' => 70, 'desc' => 'Ven pongal with 2 crispy vadas', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Mini Meals', 'cat' => 'south-indian-tiffin', 'price' => 80, 'desc' => 'Rice with sambar, rasam, kootu, poriyal, curd', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Onion Dosa', 'cat' => 'south-indian-dosa-varieties', 'price' => 50, 'desc' => 'Dosa with onions and sambar', 'jain' => false, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Paper Roast', 'cat' => 'south-indian-dosa-varieties', 'price' => 45, 'desc' => 'Ultra thin crispy paper dosa', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
        ]);

        // ── Provider 3: Pudukkottai Biryani House (Veg Biryani) ────
        $biryani = $this->createProvider(
            userId: $this->getUserId('9000000005'),
            type: 'homemade',
            name: 'Pudukkottai Biryani House',
            desc: 'Veg biryani specialists. Dum biryani, brinjal curry, raita. Pure veg, no compromise on taste.',
            address: 'Near Bus Stand, Pudukkottai',
            rating: 4.5,
            orders: 189
        );
        $this->createMenu($biryani, $categories, [
            ['name' => 'Veg Biryani', 'cat' => 'biryani-veg', 'price' => 100, 'desc' => 'Dum cooked veg biryani with raita', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Paneer Biryani', 'cat' => 'biryani-veg', 'price' => 130, 'desc' => 'Biryani with paneer pieces and mint raita', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Brinjal Curry', 'cat' => 'north-indian-curry-gravy', 'price' => 80, 'desc' => 'Ennai kathirikai - spicy brinjal curry', 'jain' => true, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Paneer Butter Masala', 'cat' => 'north-indian-paneer-specialties', 'price' => 120, 'desc' => 'Creamy tomato gravy with soft paneer', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Palak Paneer', 'cat' => 'north-indian-paneer-specialties', 'price' => 110, 'desc' => 'Spinach curry with paneer', 'jain' => false, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Jeera Rice', 'cat' => 'north-indian-paratha-roti', 'price' => 70, 'desc' => 'Cumin flavored basmati rice', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Raita', 'cat' => 'north-indian-curry-gravy', 'price' => 30, 'desc' => 'Curd raita with onions and cucumber', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
        ]);

        // ── Provider 4: Street Food Junction ────────────────────────
        $street = $this->createProvider(
            userId: $this->getUserId('9000000006'),
            type: 'homemade',
            name: 'Street Food Junction',
            desc: 'Mumbai style street food in Pudukkottai. Pani puri, samosa, chaat, pav bhaji!',
            address: 'Meivazhisalai Road, Pudukkottai',
            rating: 4.4,
            orders: 267
        );
        $this->createMenu($street, $categories, [
            ['name' => 'Pani Puri (6 pcs)', 'cat' => 'street-food-pani-puri-chaat', 'price' => 30, 'desc' => 'Crispy puris with spicy mint water', 'jain' => true, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Pav Bhaji', 'cat' => 'street-food-pav-bhaji', 'price' => 70, 'desc' => 'Mashed vegetable curry with butter pav', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Vada Pav', 'cat' => 'street-food-vada-pav', 'price' => 35, 'desc' => 'Spicy potato vada in bun with chutneys', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Samosa (2 pcs)', 'cat' => 'street-food-samosa-kachori', 'price' => 30, 'desc' => 'Crispy samosas with tamarind chutney', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Bhel Puri', 'cat' => 'street-food-bhel-puri', 'price' => 35, 'desc' => 'Puffed rice mix with chutneys and sev', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Dahi Puri (6 pcs)', 'cat' => 'street-food-pani-puri-chaat', 'price' => 40, 'desc' => 'Puris filled with curd and sweet chutney', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Sev Puri', 'cat' => 'street-food-pani-puri-chaat', 'price' => 35, 'desc' => 'Crispy puris with potatoes, chutneys and sev', 'jain' => true, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Masala Chai', 'cat' => 'beverages-masala-chai', 'price' => 15, 'desc' => 'Hot spiced tea with ginger', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
        ]);

        // ── Provider 5: Sweet Palace (Desserts & Mithai) ────────────
        $sweets = $this->createProvider(
            userId: $this->getUserId('9000000007'),
            type: 'homemade',
            name: 'Sweet Palace',
            desc: 'Premium Pudukkottai mithai and desserts. Made with pure ghee. Special Diwali orders.',
            address: 'Pudukkottai Town',
            rating: 4.9,
            orders: 412
        );
        $this->createMenu($sweets, $categories, [
            ['name' => 'Gulab Jamun (4 pcs)', 'cat' => 'desserts-indian-mithai', 'price' => 40, 'desc' => 'Hot gulab jamuns in sugar syrup', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Rasgulla (4 pcs)', 'cat' => 'desserts-indian-mithai', 'price' => 40, 'desc' => 'Spongy cheese balls in sugar syrup', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Jalebi (4 pcs)', 'cat' => 'desserts-indian-mithai', 'price' => 35, 'desc' => 'Hot crispy jalebis with saffron', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Kheer', 'cat' => 'desserts-halwa-kheer', 'price' => 40, 'desc' => 'Creamy rice payasam with cardamom and dry fruits', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'bowl'],
            ['name' => 'Moong Dal Halwa', 'cat' => 'desserts-halwa-kheer', 'price' => 60, 'desc' => 'Rich moong dal halwa with ghee and dry fruits', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Kulfi (2 pcs)', 'cat' => 'desserts-ice-cream-kulfi', 'price' => 35, 'desc' => 'Traditional malai kulfi with pistachios', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Payasam', 'cat' => 'desserts-halwa-kheer', 'price' => 40, 'desc' => 'Vermicelli payasam with cashews and raisins', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'bowl'],
        ]);

        // ── Provider 6: Jain Bhojanalay ─────────────────────────────
        $jain = $this->createProvider(
            userId: $this->getUserId('9000000008'),
            type: 'homemade',
            name: 'Jain Bhojanalay',
            desc: '100% Jain food - no onion, no garlic, no root vegetables. Pure and sattvic.',
            address: 'Meivazhisalai, Pudukkottai',
            rating: 4.7,
            orders: 156
        );
        $this->createMenu($jain, $categories, [
            ['name' => 'Jain Thali', 'cat' => 'south-indian-meals-thali', 'price' => 100, 'desc' => 'Complete Jain thali without onion garlic', 'jain' => true, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Jain Dosa', 'cat' => 'south-indian-dosa-varieties', 'price' => 50, 'desc' => 'Plain dosa without onion', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Jain Sambar', 'cat' => 'south-indian-meals-thali', 'price' => 40, 'desc' => 'Sambar without onion garlic, with drumstick', 'jain' => true, 'vegan' => true, 'spice' => 'medium', 'unit' => 'bowl'],
            ['name' => 'Jain Idli (4 pcs)', 'cat' => 'south-indian-idli-vada', 'price' => 35, 'desc' => 'Soft idlis with Jain chutney', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Jain Pongal', 'cat' => 'south-indian-tiffin', 'price' => 45, 'desc' => 'Ven pongal without onion garlic', 'jain' => true, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Jain Sandwich', 'cat' => 'snacks-chaat-namkeen-mixture', 'price' => 50, 'desc' => 'Grilled sandwich with Jain filling', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
        ]);

        // ── Provider 7: Chinese Wok ─────────────────────────────────
        $chinese = $this->createProvider(
            userId: $this->getUserId('9000000009'),
            type: 'homemade',
            name: 'Chinese Wok',
            desc: 'Indo-Chinese veg food. Noodles, fried rice, manchurian, chilli paneer!',
            address: 'Pudukkottai Town',
            rating: 4.3,
            orders: 178
        );
        $this->createMenu($chinese, $categories, [
            ['name' => 'Veg Noodles', 'cat' => 'chinese-veg-noodles-chow-mein', 'price' => 80, 'desc' => 'Stir-fried noodles with vegetables and soy sauce', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Veg Fried Rice', 'cat' => 'chinese-veg-fried-rice', 'price' => 80, 'desc' => 'Wok-fried rice with vegetables and spring onions', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Gobi Manchurian', 'cat' => 'chinese-veg-manchurian', 'price' => 90, 'desc' => 'Crispy cauliflower in tangy manchurian sauce', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Veg Manchurian Gravy', 'cat' => 'chinese-veg-manchurian', 'price' => 100, 'desc' => 'Vegetable balls in spicy manchurian gravy', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Chilli Paneer', 'cat' => 'chinese-veg-starters', 'price' => 110, 'desc' => 'Spicy paneer dry with capsicum and onions', 'jain' => false, 'vegan' => false, 'spice' => 'very_spicy', 'unit' => 'plate'],
            ['name' => 'Hakka Noodles', 'cat' => 'chinese-veg-noodles-chow-mein', 'price' => 85, 'desc' => 'Spicy hakka style noodles', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
        ]);

        // ── Provider 8: Royal Caterers (Catering) ───────────────────
        $catering = $this->createProvider(
            userId: $this->getUserId('9000000010'),
            type: 'catering',
            name: 'Royal Caterers',
            desc: 'Premium veg catering for weddings, parties, and events in Pudukkottai district. 200+ events served.',
            address: 'Pudukkottai Main Road',
            rating: 4.7,
            orders: 89
        );
        $this->createMenu($catering, $categories, [
            ['name' => 'Wedding Veg Thali', 'cat' => 'wedding-catering-veg-thali-package', 'price' => 250, 'desc' => '12-course vegetarian wedding thali per person', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Live Counter - Dosa', 'cat' => 'wedding-catering-live-counter', 'price' => 100, 'desc' => 'Live dosa counter per person (min 50 guests)', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Welcome Drinks Package', 'cat' => 'wedding-catering-welcome-drinks', 'price' => 50, 'desc' => 'Assorted welcome drinks per person', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Party Snacks Package', 'cat' => 'party-package-kitty-party', 'price' => 80, 'desc' => 'Samosa, pakoda, cutlet, tea per person', 'jain' => false, 'vegan' => true, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Birthday Package', 'cat' => 'party-package-birthday-party', 'price' => 150, 'desc' => 'Complete birthday party food per person', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Full Day Catering', 'cat' => 'corporate-catering-full-day-package', 'price' => 350, 'desc' => 'Breakfast + Lunch + Snacks per person', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
        ]);

        // ── Provider 9: Hotel Meena (Hotel Food) ────────────────────
        $hotel = $this->createProvider(
            userId: $this->getUserId('9000000011'),
            type: 'hotel',
            name: 'Hotel Meena',
            desc: 'Pudukkottai hotel with restaurant and room service. Pure veg dining. Buffet on Sundays.',
            address: 'Pudukkottai - Trichy Road',
            rating: 4.6,
            orders: 134
        );
        $this->createMenu($hotel, $categories, [
            ['name' => 'South Indian Breakfast', 'cat' => 'room-service-breakfast', 'price' => 120, 'desc' => 'Idli, vada, pongal, dosa, filter coffee', 'jain' => false, 'vegan' => false, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Meals', 'cat' => 'room-service-lunch', 'price' => 150, 'desc' => 'Complete Tamil meals with payasam', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Veg Thali', 'cat' => 'room-service-lunch', 'price' => 130, 'desc' => 'North Indian veg thali', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Veg Buffet', 'cat' => 'buffet-veg-buffet', 'price' => 250, 'desc' => 'Unlimited veg buffet - Sunday special', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Room Service Dinner', 'cat' => 'room-service-dinner', 'price' => 180, 'desc' => 'Complete dinner served in room', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Special Meals', 'cat' => 'room-service-lunch', 'price' => 200, 'desc' => 'Special festival meals with extra items', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
        ]);

        // ── Provider 10: Parotta Kadai ──────────────────────────────
        $parotta = $this->createProvider(
            userId: $this->getUserId('9000000012'),
            type: 'homemade',
            name: 'Parotta Kadai',
            desc: 'Famous parotta and salna. Layered parottas with spicy vegetable salna. Late night food!',
            address: 'Meivazhisalai, Pudukkottai',
            rating: 4.5,
            orders: 289
        );
        $this->createMenu($parotta, $categories, [
            ['name' => 'Parotta (2 pcs)', 'cat' => 'south-indian-dosa-varieties', 'price' => 30, 'desc' => 'Flaky layered parottas', 'jain' => true, 'vegan' => true, 'spice' => 'mild', 'unit' => 'plate'],
            ['name' => 'Parotta Salna', 'cat' => 'south-indian-meals-thali', 'price' => 50, 'desc' => '2 parottas with spicy vegetable salna', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Kothu Parotta', 'cat' => 'south-indian-meals-thali', 'price' => 70, 'desc' => 'Shredded parotta stir-fried with vegetables', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
            ['name' => 'Egg Parotta', 'cat' => 'south-indian-meals-thali', 'price' => 60, 'desc' => 'Parotta with egg and salna', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Veg Kurma', 'cat' => 'south-indian-meals-thali', 'price' => 60, 'desc' => 'Mixed vegetable kurma with parotta', 'jain' => false, 'vegan' => false, 'spice' => 'medium', 'unit' => 'plate'],
            ['name' => 'Chickenless Curry', 'cat' => 'south-indian-meals-thali', 'price' => 80, 'desc' => 'Soya chunk curry with parotta (tastes like chicken)', 'jain' => false, 'vegan' => true, 'spice' => 'spicy', 'unit' => 'plate'],
        ]);

        $this->command->info("Seeded 10 providers in Meivazhisalai/Pudukkottai with " . FoodItem::count() . " items");
    }

    private function createProvider(int $userId, string $type, string $name, string $desc, string $address, float $rating, int $orders): FoodProvider
    {
        // Add small random offset to base coordinates
        $lat = $this->baseLat + (rand(-20, 20) / 1000);
        $lng = $this->baseLng + (rand(-20, 20) / 1000);

        return FoodProvider::create([
            'user_id' => $userId,
            'provider_type' => $type,
            'business_name' => $name,
            'description' => $desc,
            'phone' => '9' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
            'email' => strtolower(str_replace([' ', "'"], '', $name)) . '@example.com',
            'address' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
            'city' => 'Pudukkottai',
            'state' => 'Tamil Nadu',
            'pincode' => '622' . rand(100, 999),
            'fssai_license' => 'FSSAI' . rand(1000000000, 9999999999),
            'verification_status' => 'approved',
            'verified_at' => now(),
            'is_active' => true,
            'is_featured' => $rating >= 4.7,
            'avg_rating' => $rating,
            'total_orders' => $orders,
            'total_revenue' => $orders * rand(60, 150),
            'commission_rate' => $type === 'catering' ? 8.00 : ($type === 'hotel' ? 12.00 : 10.00),
            'delivery_radius_km' => 5,
            'min_order_amount' => 30,
            'operating_hours' => json_encode([
                'mon' => ['open' => '07:00', 'close' => '22:00'],
                'tue' => ['open' => '07:00', 'close' => '22:00'],
                'wed' => ['open' => '07:00', 'close' => '22:00'],
                'thu' => ['open' => '07:00', 'close' => '22:00'],
                'fri' => ['open' => '07:00', 'close' => '22:00'],
                'sat' => ['open' => '07:00', 'close' => '22:00'],
                'sun' => ['open' => '08:00', 'close' => '21:00'],
            ]),
        ]);
    }

    private function createMenu(FoodProvider $provider, array $categories, array $items): void
    {
        foreach ($items as $item) {
            $catId = $categories[$item['cat']] ?? $categories['home-cooked-daily-tiffin'] ?? 1;

            $foodItem = FoodItem::create([
                'provider_id' => $provider->id,
                'category_id' => $catId,
                'name' => $item['name'],
                'slug' => \Illuminate\Support\Str::slug($item['name']) . '-' . $provider->id,
                'description' => $item['desc'],
                'image_url' => '/images/food/default.jpg',
                'price' => $item['price'],
                'discount_price' => rand(0, 10) > 7 ? round($item['price'] * 0.85) : null,
                'unit' => $item['unit'],
                'min_quantity' => 1,
                'max_quantity' => 50,
                'preparation_time_min' => rand(10, 30),
                'is_jain' => $item['jain'],
                'is_vegan' => $item['vegan'],
                'spice_level' => $item['spice'],
                'allergens' => $item['jain'] ? json_encode([]) : json_encode(['gluten']),
                'ingredients' => 'Fresh local ingredients',
                'is_available' => true,
                'is_featured' => rand(0, 10) > 6,
                'available_days' => json_encode(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']),
                'available_from' => '08:00',
                'available_to' => '22:00',
                'total_orders' => rand(10, 100),
                'avg_rating' => round(3.5 + rand(0, 15) / 10, 1),
            ]);

            // Pricing tiers
            FoodPricingTier::create([
                'food_item_id' => $foodItem->id,
                'tier_name' => 'Regular',
                'quantity' => 1,
                'unit' => 'plate',
                'price' => $item['price'],
            ]);
            FoodPricingTier::create([
                'food_item_id' => $foodItem->id,
                'tier_name' => 'Family Pack',
                'quantity' => 4,
                'unit' => 'plates',
                'price' => $item['price'] * 3.5,
            ]);

            // Delivery slots (lunch + dinner)
            for ($day = 0; $day < 7; $day++) {
                FoodDeliverySlot::create([
                    'provider_id' => $provider->id,
                    'day_of_week' => $day,
                    'slot_start' => '12:00',
                    'slot_end' => '14:00',
                    'max_orders' => 20,
                    'current_orders' => rand(0, 15),
                    'is_active' => true,
                ]);
                FoodDeliverySlot::create([
                    'provider_id' => $provider->id,
                    'day_of_week' => $day,
                    'slot_start' => '19:00',
                    'slot_end' => '21:00',
                    'max_orders' => 25,
                    'current_orders' => rand(0, 20),
                    'is_active' => true,
                ]);
            }
        }
    }

    private function getUserId(string $phone): int
    {
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Provider_' . substr($phone, -4),
                'phone' => $phone,
                'email' => 'provider' . substr($phone, -4) . '@example.com',
                'password' => bcrypt('password'),
                'status' => 'active',
                'is_admin' => false,
            ]);
        }
        return $user->id;
    }
}
