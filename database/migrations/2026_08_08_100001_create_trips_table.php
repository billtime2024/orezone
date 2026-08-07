<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('origin_name');
            $table->decimal('origin_lat', 10, 7)->nullable();
            $table->decimal('origin_lng', 10, 7)->nullable();
            $table->string('destination_name');
            $table->decimal('destination_lat', 10, 7)->nullable();
            $table->decimal('destination_lng', 10, 7)->nullable();
            $table->dateTime('departure_at');
            $table->dateTime('arrival_at')->nullable();
            $table->integer('total_seats');
            $table->integer('available_seats');
            $table->string('booking_mode')->default('instant'); // instant, request_approval
            $table->string('status')->default('draft'); // draft, published, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'departure_at']);
            $table->index(['origin_name', 'destination_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
