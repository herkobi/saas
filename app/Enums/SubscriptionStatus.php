<?php

/**
 * Subscription Status Enum
 *
 * Defines the possible states of a subscription plan.
 * Separates billing status from the tenant's lifecycle status.
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

enum SubscriptionStatus: string
{
    /**
     * Subscription is active and payments are up to date.
     */
    case ACTIVE = 'active';

    /**
     * Subscription is currently in the trial period.
     */
    case TRIALING = 'trialing';

    /**
     * Subscription was cancelled by the user but the period has not ended yet.
     * (Grace period / Cancelled but still has access).
     */
    case CANCELED = 'canceled';

    /**
     * Payment is past due but the system allows access (Grace Period).
     */
    case PAST_DUE = 'past_due';

    /**
     * Subscription has expired and was not renewed. Service is stopped.
     */
    case EXPIRED = 'expired';

    /**
     * Get the badge color variant for UI display.
     *
     * @return string
     */
    public function badge(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TRIALING => 'info',
            self::CANCELED => 'warning', // Yellow: Warning (Still has access but won't renew)
            self::PAST_DUE => 'danger',  // Red: Payment issue
            self::EXPIRED => 'secondary', // Gray: Passive
        };
    }

    /**
     * Get the human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::TRIALING => 'Deneme Süresi',
            self::CANCELED => 'İptal Edildi (Süresi Var)',
            self::PAST_DUE => 'Ödeme Gecikti',
            self::EXPIRED => 'Süresi Doldu',
        };
    }

    /**
     * Determine if this subscription status allows system access.
     * (Active, Trialing, Cancelled but valid, Past Due tolerance).
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return match ($this) {
            self::ACTIVE,
            self::TRIALING,
            self::CANCELED,
            self::PAST_DUE => true,
            self::EXPIRED => false,
        };
    }
}
