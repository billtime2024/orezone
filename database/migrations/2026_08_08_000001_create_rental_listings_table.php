<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('rental_type', ['house', 'car', 'commercial', 'room']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();

            // Pricing
            $table->decimal('price_per_unit', 12, 2);
            $table->enum('price_unit', ['hour', 'day', 'month', 'year'])->default('month');
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('cleaning_fee', 12, 2)->default(0);

            // Location
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Availability
            $table->enum('status', ['draft', 'active', 'paused', 'closed'])->default('draft');
            $table->boolean('instant_booking')->default(false);
            $table->json('blocked_dates')->nullable();

            // Media
            $table->json('photos')->nullable();
            $table->json('rules')->nullable();

            // Counts (denormalized for search performance)
            $table->unsignedInteger('total_bookings')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_type', 'status']);
            $table->index(['city', 'status']);
            $table->index(['user_id', 'rental_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_listings');
    }
};
