<?php

/**
 * Setting Service Interface
 *
 * Defines the contract for setting management operations
 * in the panel context.
 *
 * @package    App\Contracts\Panel\Settings
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Panel\Settings;

/**
 * Interface for setting service operations.
 *
 * Provides methods for retrieving and updating system settings
 * with caching support.
 */
interface SettingServiceInterface
{
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Get all public settings.
     *
     * @return array<string, mixed>
     */
    public function getAllPublic(): array;

    /**
     * Get all settings as key-value pairs.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Update multiple settings at once.
     *
     * @param array<string, mixed> $settings
     * @return void
     */
    public function updateMany(array $settings): void;

    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public function clearCache(): void;
}
