<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_booking_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type')->nullable(); // guest, host, system, admin
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['rental_booking_id', 'created_at'], 'rbsh_booking_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_booking_status_histories');
    }
};
