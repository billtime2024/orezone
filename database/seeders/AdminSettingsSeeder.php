<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'platform_fees', 'key' => 'percentage', 'value' => '10', 'description' => 'Platform fee percentage (e.g. 10 = 10%)'],
            ['group' => 'platform_fees', 'key' => 'flat_fee', 'value' => '0', 'description' => 'Flat platform fee in INR (0 = use percentage only)'],
            ['group' => 'platform_fees', 'key' => 'tax_percentage', 'value' => '18', 'description' => 'GST/tax percentage on platform fee'],
            ['group' => 'cancellation', 'key' => 'traveler_before_accept_refund', 'value' => '100', 'description' => 'Refund % for traveler cancel before host accepts'],
            ['group' => 'cancellation', 'key' => 'traveler_after_confirm_refund', 'value' => '50', 'description' => 'Refund % for traveler cancel after confirmation'],
            ['group' => 'cancellation', 'key' => 'host_cancel_penalty', 'value' => '0', 'description' => 'Penalty % charged to host for cancelling'],
            ['group' => 'cancellation', 'key' => 'no_show_fee_retain', 'value' => '100', 'description' => '% of platform fee retained on no-show'],
            ['group' => 'search', 'key' => 'default_radius_km', 'value' => '50', 'description' => 'Default search radius in km'],
            ['group' => 'verification', 'key' => 'require_aadhaar', 'value' => '0', 'description' => 'Require Aadhaar reference for host verification (0=no, 1=yes)'],
            ['group' => 'verification', 'key' => 'document_expiry_days', 'value' => '365', 'description' => 'Days before verification documents expire'],
            ['group' => 'features', 'key' => 'instant_booking_enabled', 'value' => '1', 'description' => 'Enable instant booking mode'],
            ['group' => 'features', 'key' => 'request_approval_enabled', 'value' => '1', 'description' => 'Enable request-approval booking mode'],
            ['group' => 'features', 'key' => 'wallet_topup_enabled', 'value' => '0', 'description' => 'Enable wallet top-up (requires payment gateway)'],
            ['group' => 'features', 'key' => 'sos_notify_emergency_contacts', 'value' => '1', 'description' => 'Notify emergency contacts on SOS trigger'],
        ];

        foreach ($settings as $setting) {
            DB::table('admin_settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
