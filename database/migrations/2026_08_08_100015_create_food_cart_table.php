<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_cart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_tier_id')->nullable()->constrained('food_pricing_tiers')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->string('special_notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'food_item_id', 'pricing_tier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_cart');
    }
};
