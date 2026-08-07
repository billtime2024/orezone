<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add missing columns
            $table->foreignId('host_id')->after('traveler_id')->constrained('users');
            $table->renameColumn('seats_booked', 'seat_count');
            $table->foreignId('pickup_stop_id')->nullable()->after('seat_count')->constrained('trip_stops');
            $table->foreignId('drop_stop_id')->nullable()->after('pickup_stop_id')->constrained('trip_stops');
            $table->decimal('platform_fee', 10, 2)->default(0)->after('status');
            $table->decimal('platform_fee_tax', 10, 2)->default(0)->after('platform_fee');
            $table->decimal('total_platform_fee', 10, 2)->default(0)->after('platform_fee_tax');
            $table->json('fee_snapshot')->nullable()->after('total_platform_fee');
            $table->string('idempotency_key')->nullable()->unique()->after('fee_snapshot');
            $table->text('notes')->nullable()->after('idempotency_key');
            $table->timestamp('requested_at')->nullable()->after('notes');
            $table->timestamp('accepted_at')->nullable()->after('requested_at');
            $table->timestamp('confirmed_at')->nullable()->after('accepted_at');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');

            // Drop the old total_fee column
            $table->dropColumn('total_fee');

            // Add indexes
            $table->index('host_id');
            $table->index('status');
            $table->index('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['host_id']);
            $table->dropColumn('host_id');
            $table->renameColumn('seat_count', 'seats_booked');
            $table->dropForeign(['pickup_stop_id']);
            $table->dropColumn('pickup_stop_id');
            $table->dropForeign(['drop_stop_id']);
            $table->dropColumn('drop_stop_id');
            $table->dropColumn(['platform_fee', 'platform_fee_tax', 'total_platform_fee', 'fee_snapshot', 'idempotency_key']);
            $table->dropColumn(['requested_at', 'accepted_at', 'confirmed_at', 'cancelled_at', 'completed_at']);
            $table->decimal('total_fee', 10, 2)->default(0);
        });
    }
};
