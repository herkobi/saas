<?php

/**
 * User Status Enum
 *
 * Defines the available statuses for users within the system,
 * providing type-safe status management with helpers.
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

enum UserStatus: int
{
    /**
     * User cannot login. Can only be activated by an administrator.
     */
    case PASSIVE = 0;

    /**
     * User has full functionality and access to the system.
     */
    case ACTIVE = 1;

    /**
     * User can login but has restricted access.
     * C/U/D (Create, Update, Delete) operations are disabled. Read-only access.
     */
    case DRAFT = 2;

    /**
     * Get badge variant for UI display.
     *
     * @return string The badge variant class name
     */
    public function badge(): string
    {
        return match ($this) {
            self::PASSIVE => 'secondary',
            self::ACTIVE => 'success',
            self::DRAFT => 'warning',
        };
    }

    /**
     * Get human-readable label for the user status.
     *
     * @return string The localized label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PASSIVE => 'Pasif',
            self::ACTIVE => 'Aktif',
            self::DRAFT => 'Taslak (Kısıtlı)',
        };
    }

    /**
     * Check if status is active.
     *
     * @return bool True if status is active, false otherwise
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the user status allows login.
     *
     * @return bool
     */
    public function canLogin(): bool
    {
        return match ($this) {
            self::ACTIVE, self::DRAFT => true,
            self::PASSIVE => false,
        };
    }

    /**
     * Check if the user status implies read-only access.
     * (e.g., DRAFT status)
     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Get all available options for forms.
     *
     * @return array Array of status values mapped to their labels
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
