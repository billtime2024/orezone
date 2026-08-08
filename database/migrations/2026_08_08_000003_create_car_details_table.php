<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('color')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['manual', 'automatic']);
            $table->unsignedTinyInteger('seats')->default(5);
            $table->boolean('self_drive')->default(true);
            $table->boolean('with_driver')->default(false);
            $table->decimal('driver_charge_per_day', 10, 2)->default(0);
            $table->unsignedInteger('mileage_km')->nullable();
            $table->string('registration_number')->nullable();
            $table->json('insurance_details')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_details');
    }
};
