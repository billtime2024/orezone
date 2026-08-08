<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['available', 'blocked', 'booked'])->default('available');
            $table->decimal('price_override', 12, 2)->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['rental_listing_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_availability');
    }
};
