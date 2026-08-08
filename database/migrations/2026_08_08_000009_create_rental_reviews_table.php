<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->json('ratings')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['rental_listing_id', 'rental_booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_reviews');
    }
};
