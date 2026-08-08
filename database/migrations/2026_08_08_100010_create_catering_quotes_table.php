<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('food_providers')->cascadeOnDelete();
            $table->decimal('quoted_amount', 12, 2);
            $table->json('proposed_menu')->nullable();
            $table->boolean('includes_decor')->default(false);
            $table->boolean('includes_service_staff')->default(false);
            $table->integer('staff_count')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('valid_until');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_quotes');
    }
};
