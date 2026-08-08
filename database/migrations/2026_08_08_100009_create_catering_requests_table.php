<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('food_providers')->cascadeOnDelete();
            $table->enum('event_type', ['wedding', 'birthday', 'corporate', 'party', 'festival', 'other']);
            $table->string('event_name');
            $table->date('event_date');
            $table->date('event_end_date')->nullable();
            $table->time('event_time');
            $table->text('venue_address');
            $table->decimal('venue_latitude', 10, 7);
            $table->decimal('venue_longitude', 10, 7);
            $table->integer('guest_count');
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->json('cuisine_preferences')->nullable();
            $table->json('dietary_requirements')->nullable();
            $table->text('menu_description')->nullable();
            $table->text('special_requests')->nullable();
            $table->boolean('tasting_requested')->default(false);
            $table->date('tasting_date')->nullable();
            $table->enum('status', [
                'pending', 'quotes_received', 'quote_selected',
                'tasting_scheduled', 'confirmed', 'in_progress',
                'completed', 'cancelled'
            ])->default('pending');
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('advance_paid', 10, 2)->default(0);
            $table->enum('payment_status', [
                'pending', 'advance_paid', 'partially_paid',
                'fully_paid', 'refunded'
            ])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_requests');
    }
};
