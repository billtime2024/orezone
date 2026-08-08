<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_tier_id')->nullable()->constrained('food_pricing_tiers')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->integer('quantity');
            $table->decimal('total', 10, 2);
            $table->string('special_notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_order_items');
    }
};
