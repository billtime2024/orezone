<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_service_id')->constrained('hotel_food_services')->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('party_size');
            $table->text('special_requests')->nullable();
            $table->enum('status', [
                'pending', 'confirmed', 'seated',
                'completed', 'cancelled', 'no_show'
            ])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
};
