<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status');
        });

        Schema::table('verification_documents', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('verification_documents', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
