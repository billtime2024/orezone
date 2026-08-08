<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('food_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->enum('unit', ['plate', 'bowl', 'kg', 'ltr', 'dozen', 'parcel']);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(50);
            $table->integer('preparation_time_min');
            $table->boolean('is_jain')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->enum('spice_level', ['mild', 'medium', 'spicy', 'very_spicy']);
            $table->json('allergens')->nullable();
            $table->text('ingredients')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('available_days')->nullable();
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->timestamps();

            $table->unique(['provider_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
