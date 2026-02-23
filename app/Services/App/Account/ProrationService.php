<?php

/**
 * Proration Service
 *
 * This service handles proration calculations for subscription upgrades.
 * It calculates credits based on unused days and new plan amounts.
 *
 * @package    App\Services\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Enums\ProrationType;
use App\Models\PlanPrice;
use App\Models\Subscription;

/**
 * Service for calculating proration amounts.
 *
 * Provides methods for calculating credits from unused subscription days
 * and determining the final amount for plan upgrades.
 */
class ProrationService
{
    /**
     * Calculate proration details for a subscription upgrade.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return array{credit: float, new_amount: float, final_amount: float, days_remaining: int, daily_rate_old: float, daily_rate_new: float}
     */
    public function calculate(Subscription $subscription, PlanPrice $newPlanPrice, ProrationType $type = ProrationType::IMMEDIATE): array
    {
        if (!$type->isImmediate()) {
            return [
                'credit' => 0,
                'new_amount' => (float) $newPlanPrice->price,
                'final_amount' => (float) $newPlanPrice->price,
                'days_remaining' => $this->getDaysRemaining($subscription),
                'daily_rate_old' => 0,
                'daily_rate_new' => 0,
                'proration_type' => $type->value,
            ];
        }

        $daysRemaining = $this->getDaysRemaining($subscription);
        $totalDays = $this->getTotalDays($subscription);

        if ($totalDays <= 0 || $daysRemaining <= 0) {
            return [
                'credit' => 0,
                'new_amount' => (float) $newPlanPrice->price,
                'final_amount' => (float) $newPlanPrice->price,
                'days_remaining' => 0,
                'daily_rate_old' => 0,
                'daily_rate_new' => 0,
            ];
        }

        $currentPrice = (float) $subscription->price->price;
        $newPrice = (float) $newPlanPrice->price;

        $dailyRateOld = $currentPrice / $totalDays;
        $credit = round($dailyRateOld * $daysRemaining, 2);

        $newTotalDays = $this->calculateNewPlanDays($newPlanPrice);
        $dailyRateNew = $newTotalDays > 0 ? $newPrice / $newTotalDays : 0;

        $finalAmount = max(0, round($newPrice - $credit, 2));

        return [
            'credit' => $credit,
            'new_amount' => $newPrice,
            'final_amount' => $finalAmount,
            'days_remaining' => $daysRemaining,
            'daily_rate_old' => round($dailyRateOld, 4),
            'daily_rate_new' => round($dailyRateNew, 4),
        ];
    }

    /**
     * Get the number of days remaining in the current subscription.
     *
     * @param Subscription $subscription The subscription
     * @return int
     */
    public function getDaysRemaining(Subscription $subscription): int
    {
        if (!$subscription->ends_at) {
            return 0;
        }

        $days = now()->diffInDays($subscription->ends_at, false);

        return max(0, (int) $days);
    }

    /**
     * Get the total number of days in the subscription period.
     *
     * @param Subscription $subscription The subscription
     * @return int
     */
    public function getTotalDays(Subscription $subscription): int
    {
        if (!$subscription->starts_at || !$subscription->ends_at) {
            return 0;
        }

        return (int) $subscription->starts_at->diffInDays($subscription->ends_at);
    }

    /**
     * Calculate the number of days for a plan price.
     *
     * @param PlanPrice $planPrice The plan price
     * @return int
     */
    public function calculateNewPlanDays(PlanPrice $planPrice): int
    {
        $interval = $planPrice->interval->value;
        $count = $planPrice->interval_count;

        return match ($interval) {
            'day' => $count,
            'week' => $count * 7,
            'month' => $count * 30,
            'year' => $count * 365,
            default => 30,
        };
    }

    /**
     * Calculate the daily rate for a subscription.
     *
     * @param Subscription $subscription The subscription
     * @return float
     */
    public function getDailyRate(Subscription $subscription): float
    {
        $totalDays = $this->getTotalDays($subscription);

        if ($totalDays <= 0) {
            return 0;
        }

        return (float) $subscription->price->price / $totalDays;
    }

    /**
     * Calculate the unused value of a subscription.
     *
     * @param Subscription $subscription The subscription
     * @return float
     */
    public function getUnusedValue(Subscription $subscription): float
    {
        $daysRemaining = $this->getDaysRemaining($subscription);
        $dailyRate = $this->getDailyRate($subscription);

        return round($dailyRate * $daysRemaining, 2);
    }
}
