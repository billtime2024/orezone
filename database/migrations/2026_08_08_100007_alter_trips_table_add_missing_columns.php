<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Add address columns
            $table->string('origin_address')->nullable()->after('origin_name');
            $table->string('destination_address')->nullable()->after('destination_name');

            // Add route polyline
            $table->text('route_polyline')->nullable()->after('notes');

            // Add indexes
            $table->index('status');
            $table->index('departure_at');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'origin_address',
                'destination_address',
                'route_polyline',
            ]);
        });
    }
};
