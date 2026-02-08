<?php

/**
 * Feature Type Enum
 *
 * Defines the different classification types for features within the subscription system.
 * Used to determine how a feature's usage is calculated and enforced.
 *
 * @package    App\Enums
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Enums;

enum FeatureType: string
{
    /**
     * Numerical limit.
     * Grants permission up to a specific quantity.
     * (e.g., 5 Users, 10GB Storage)
     */
    case LIMIT = 'limit';

    /**
     * Feature switch (Boolean).
     * Indicates whether a specific feature is included or enabled.
     * (e.g., API Access, White Labeling, Custom Reporting)
     */
    case FEATURE = 'feature';

    /**
     * Metered usage.
     * Usage counter increases with each action. Typically resets at the end of the billing cycle.
     * (e.g., 1000 Emails sent per month, 5000 API requests)
     */
    case METERED = 'metered';

    /**
     * Get the human-readable label for the feature type.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::LIMIT => 'Sayısal Limit',
            self::FEATURE => 'Özellik (Var/Yok)',
            self::METERED => 'Sayaçlı (Sıfırlanan)',
        };
    }

    /**
     * Get the description for the feature type.
     * Useful for tooltips or help text in the admin panel.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::LIMIT => 'Belirli bir sayısal sınıra kadar kullanım izni verir (Örn: 5 Kullanıcı).',
            self::FEATURE => 'Bir özelliğin açık veya kapalı olmasını belirler (Örn: API Erişimi).',
            self::METERED => 'Her kullanımda sayaç artar. Genelde fatura dönemi sonunda sıfırlanan limitler için kullanılır.',
        };
    }

    /**
     * Get the placeholder value for unlimited/no limit features.
     *
     * @return string
     */
    public static function placeholder(): string
    {
        return '∞';
    }

    /**
     * Get the badge color for the feature type.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::LIMIT => 'blue',
            self::FEATURE => 'green',
            self::METERED => 'orange',
        };
    }

    /**
     * Get the icon name for the feature type.
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::LIMIT => 'calculator',
            self::FEATURE => 'toggle-right',
            self::METERED => 'clock',
        };
    }

    public function allowedAddonTypes(): array
    {
        return match ($this) {
            self::LIMIT, self::METERED => [AddonType::INCREMENT, AddonType::UNLIMITED],
            self::FEATURE => [AddonType::BOOLEAN],
        };
    }
}
