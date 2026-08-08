<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->enum('order_type', ['homemade', 'catering', 'hotel']);
            $table->enum('status', [
                'placed', 'confirmed', 'preparing', 'ready',
                'out_for_delivery', 'delivered', 'cancelled', 'refunded'
            ])->default('placed');
            $table->enum('delivery_type', ['delivery', 'pickup']);
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->foreignId('delivery_slot_id')->nullable()->constrained('food_delivery_slots')->cascadeOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_charge', 8, 2)->default(0);
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('commission_amount', 8, 2);
            $table->enum('payment_method', ['wallet', 'upi', 'card', 'cash', 'netbanking']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference', 100)->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_orders');
    }
};
