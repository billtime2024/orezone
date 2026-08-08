<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_food_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->enum('service_type', ['room_service', 'restaurant', 'buffet', 'special_occasion']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_24hr')->default(false);
            $table->time('operating_start')->nullable();
            $table->time('operating_end')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_food_services');
    }
};
