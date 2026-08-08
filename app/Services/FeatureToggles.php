<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Feature toggles helper.
 *
 * Reads feature flags from admin_settings table (group: 'features').
 * Usage:
 *   FeatureToggles::isEnabled('instant_booking_enabled')
 *   FeatureToggles::get('search.default_radius_km', 50)
 */
class FeatureToggles
{
    /**
     * Check if a feature is enabled (value = '1').
     */
    public static function isEnabled(string $key): bool
    {
        return (string) static::get($key, '0') === '1';
    }

    /**
     * Get a configuration value from admin_settings.
     *
     * @param string $key Dot-notated key (e.g. 'platform_fees.percentage')
     * @param mixed $default Default value if not found
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $group = $parts[0] ?? '';
        $settingKey = $parts[1] ?? $key;

        $setting = DB::table('admin_settings')
            ->where('group', $group)
            ->where('key', $settingKey)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Get all settings for a group.
     */
    public static function getGroup(string $group): array
    {
        $settings = DB::table('admin_settings')
            ->where('group', $group)
            ->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Set a configuration value.
     */
    public static function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $group = $parts[0] ?? '';
        $settingKey = $parts[1] ?? $key;

        DB::table('admin_settings')->updateOrInsert(
            ['group' => $group, 'key' => $settingKey],
            ['value' => (string) $value, 'updated_at' => now()]
        );
    }
}
