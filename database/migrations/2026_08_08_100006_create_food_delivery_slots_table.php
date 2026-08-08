<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('slot_start');
            $table->time('slot_end');
            $table->integer('max_orders');
            $table->integer('current_orders')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_delivery_slots');
    }
};
