<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedTinyInteger('floors')->default(1);
            $table->boolean('furnished')->default(false);
            $table->boolean('parking')->default(false);
            $table->boolean('ac')->default(false);
            $table->boolean('wifi')->default(false);
            $table->json('amenities')->nullable();
            $table->enum('property_type', ['apartment', 'independent_house', 'villa', 'pg', 'hostel']);
            $table->unsignedInteger('area_sqft')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_details');
    }
};
