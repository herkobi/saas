<?php

/**
 * Plan Interval Enum
 *
 * Defines the billing frequency/intervals for subscription plans.
 * Used to calculate billing cycles and renewal dates.
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

enum PlanInterval: string
{
    /**
     * Monthly billing cycle.
     */
    case MONTH = 'month';

    /**
     * Yearly billing cycle.
     */
    case YEAR = 'year';

    /**
     * Daily billing cycle.
     * (Generally used for testing purposes or very short-term usage).
     */
    case DAY = 'day';

    /**
     * Get the human-readable label for the interval.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::MONTH => 'Aylık',
            self::YEAR => 'Yıllık',
            self::DAY => 'Günlük',
        };
    }

    /**
     * Get the short label for the interval.
     * Useful for price displays (e.g., $100 / mo).
     *
     * @return string
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::MONTH => 'Ay',
            self::YEAR => 'Yıl',
            self::DAY => 'Gün',
        };
    }
}
