<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_order_id')->constrained('food_orders')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('payout_status', ['pending', 'paid'])->default('pending');
            $table->timestamp('payout_date')->nullable();
            $table->timestamps();

            $table->index('food_order_id');
            $table->index('provider_id');
            $table->index('payout_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_commission_entries');
    }
};
