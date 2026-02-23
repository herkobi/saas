<?php

/**
 * Setting Service
 *
 * Handles all setting-related operations including retrieval,
 * storage, and caching of system settings.
 *
 * @package    App\Services\Panel\Settings
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Service
 *
 * Service implementation for managing system settings
 * with caching support for performance optimization.
 */
class SettingService
{
    /**
     * Cache key for all settings.
     *
     * @var string
     */
    private const CACHE_KEY = 'settings.all';

    /**
     * Cache TTL in seconds (1 hour).
     *
     * @var int
     */
    private const CACHE_TTL = 3600;

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->getCachedSettings();

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            $setting->setTypedValue($value);
            $setting->save();
            $this->clearCache();
        }
    }

    /**
     * Get all public settings.
     *
     * @return array<string, mixed>
     */
    public function getAllPublic(): array
    {
        return Setting::public()
            ->get()
            ->mapWithKeys(fn(Setting $setting) => [$setting->key => $setting->typed_value])
            ->toArray();
    }

    /**
     * Get all settings as key-value pairs.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->getCachedSettings();
    }

    /**
     * Update multiple settings at once.
     *
     * @param array<string, mixed> $settings
     * @return void
     */
    public function updateMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                $setting->setTypedValue($value);
                $setting->save();
            }
        }

        $this->clearCache();
    }

    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get all settings from cache or database.
     *
     * @return array<string, mixed>
     */
    private function getCachedSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn(Setting $setting) => [$setting->key => $setting->typed_value])
                ->toArray();
        });
    }
}
