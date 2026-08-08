<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->foreignId('food_item_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('food_order_id')->constrained('food_orders')->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->tinyInteger('taste_rating')->nullable();
            $table->tinyInteger('packaging_rating')->nullable();
            $table->tinyInteger('delivery_rating')->nullable();
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_reviews');
    }
};
