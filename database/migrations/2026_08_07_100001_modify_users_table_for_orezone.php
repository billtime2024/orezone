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
        Schema::table('users', function (Blueprint $table) {
            // Add phone as primary auth identifier
            $table->string('phone')->unique()->after('email');

            // Phone verification timestamp
            $table->timestamp('phone_verified_at')->nullable()->after('phone');

            // Preferred language with default
            $table->string('preferred_language', 10)->default('en')->after('phone_verified_at');

            // Account status: active, suspended, deactivated
            $table->string('status')->default('active')->after('preferred_language');

            // Avatar path (separate from Jetstream profile_photo_path)
            $table->string('avatar_path')->nullable()->after('status');

            // Last login tracking
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');

            // Make email nullable (phone is primary auth)
            $table->string('email')->nullable()->unique(false)->change();

            // Make password nullable (OTP-based auth)
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'preferred_language',
                'status',
                'avatar_path',
                'last_login_at',
            ]);

            // Restore email and password to non-nullable
            $table->string('email')->nullable(false)->unique()->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
