<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->enum('room_type', ['single', 'double', 'triple', 'dorm', 'suite']);
            $table->enum('stay_type', ['pg', 'hostel', 'hotel', 'homestay', 'co_living']);
            $table->boolean('meals_included')->default(false);
            $table->enum('meal_plan', ['none', 'breakfast', 'half_board', 'full_board'])->default('none');
            $table->boolean('ac')->default(false);
            $table->boolean('wifi')->default(false);
            $table->boolean('laundry')->default(false);
            $table->boolean('housekeeping')->default(false);
            $table->boolean('curfew_time')->default(false);
            $table->time('check_in_time')->default('12:00');
            $table->time('check_out_time')->default('11:00');
            $table->json('rules')->nullable();
            $table->json('common_areas')->nullable();
            $table->unsignedInteger('total_rooms')->default(1);
            $table->unsignedInteger('available_rooms')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_details');
    }
};
