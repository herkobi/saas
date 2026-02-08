<?php

/**
 * Grace Period Enum
 *
 * Defines the system behavior policies when a tenant's subscription is past due.
 * It determines the level of access restriction applied to the tenant.
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

enum GracePeriod: string
{
    /**
     * No restrictions. System works fully even if payment is overdue.
     * (e.g., for Enterprise/VIP customers)
     */
    case NONE = 'none';

    /**
     * Restricted Access. Customer can login and view data,
     * but cannot create new data or perform actions (Read-Only).
     */
    case RESTRICTED = 'restricted';

    /**
     * Full Block. Customer cannot login to the panel.
     * Can only access the payment page.
     */
    case BLOCKED = 'blocked';

    /**
     * Get the human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Kısıtlama Yok',
            self::RESTRICTED => 'Kısıtlı Erişim (Salt Okunur)',
            self::BLOCKED => 'Tam Erişim Engeli',
        };
    }

    /**
     * Get the description for the status (Useful for tooltips).
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::NONE => 'Müşteri borçlu olsa dahi tüm özellikleri kullanmaya devam eder.',
            self::RESTRICTED => 'Müşteri sisteme girebilir ancak yeni veri girişi yapamaz. Sadece mevcut verileri görüntüler.',
            self::BLOCKED => 'Müşterinin sisteme girişi tamamen engellenir. Sadece ödeme ekranını görür.',
        };
    }

    /**
     * Get the badge color for UI display.
     *
     * @return string
     */
    public function badge(): string
    {
        return match ($this) {
            self::NONE => 'success',
            self::RESTRICTED => 'warning',
            self::BLOCKED => 'destructive',
        };
    }
}
