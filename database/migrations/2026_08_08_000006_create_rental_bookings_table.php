<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            // Dates
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('nights');

            // Pricing (snapshot at booking time)
            $table->decimal('price_per_unit', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('cleaning_fee', 12, 2)->default(0);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('INR');

            // Status
            $table->enum('status', [
                'pending', 'confirmed', 'active', 'completed',
                'cancelled_by_guest', 'cancelled_by_host', 'rejected', 'expired', 'disputed'
            ])->default('pending');

            // Payment
            $table->enum('payment_status', [
                'pending', 'authorized', 'captured', 'partial_refund',
                'full_refund', 'refunded', 'failed'
            ])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();

            // Communication
            $table->text('guest_message')->nullable();
            $table->text('host_message')->nullable();
            $table->enum('booking_type', ['instant', 'request'])->default('instant');

            // Cancellation
            $table->text('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Metadata
            $table->unsignedInteger('guests_count')->default(1);
            $table->json('special_requests')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_listing_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
    }
};
