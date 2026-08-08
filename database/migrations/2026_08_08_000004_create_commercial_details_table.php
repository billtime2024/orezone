<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->enum('property_type', ['office', 'shop', 'warehouse', 'godown', 'showroom', 'co_working']);
            $table->unsignedInteger('area_sqft');
            $table->unsignedInteger('carpet_area_sqft')->nullable();
            $table->boolean('furnished')->default(false);
            $table->boolean('ac')->default(false);
            $table->boolean('power_backup')->default(false);
            $table->boolean('parking')->default(false);
            $table->unsignedInteger('parking_slots')->default(0);
            $table->unsignedInteger('floor_number')->nullable();
            $table->unsignedInteger('total_floors')->nullable();
            $table->boolean('lift')->default(false);
            $table->json('facilities')->nullable();
            $table->decimal('maintenance_charge', 10, 2)->default(0);
            $table->enum('lease_type', ['bare_shell', 'fitted', 'semi_furnished', 'fully_furnished']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_details');
    }
};
