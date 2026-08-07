<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Sedan', 'slug' => 'sedan'],
            ['name' => 'SUV', 'slug' => 'suv'],
            ['name' => 'Hatchback', 'slug' => 'hatchback'],
            ['name' => 'Auto', 'slug' => 'auto'],
            ['name' => 'Van', 'slug' => 'van'],
            ['name' => 'Bike', 'slug' => 'bike'],
            ['name' => 'Tempo Traveller', 'slug' => 'tempo-traveller'],
        ];

        foreach ($categories as $category) {
            VehicleCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
