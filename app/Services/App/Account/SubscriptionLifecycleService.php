<?php

/**
 * Subscription Lifecycle Service
 *
 * This service handles subscription lifecycle management operations including
 * expiration checks and scheduled downgrades.
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

use App\Contracts\App\Account\SubscriptionLifecycleServiceInterface;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for managing subscription lifecycle events.
 *
 * Provides methods for tracking expiring subscriptions and
 * processing scheduled plan changes.
 */
class SubscriptionLifecycleService implements SubscriptionLifecycleServiceInterface
{
    /**
     * Get subscriptions expiring in the specified number of days.
     *
     * @param int $daysAhead Number of days ahead to check
     * @return Collection Collection of expiring subscriptions
     */
    public function getExpiringSubscriptions(int $daysAhead): Collection
    {
        $targetDate = now()->addDays($daysAhead)->startOfDay();

        return Subscription::with(['tenant.owner', 'price.plan'])
            ->whereDate('ends_at', $targetDate)
            ->whereNull('canceled_at')
            ->whereNull('next_plan_price_id')
            ->get();
    }

    /**
     * Get subscriptions with scheduled downgrades ready to process.
     *
     * @return Collection Collection of subscriptions with pending downgrades
     */
    public function getSubscriptionsWithScheduledDowngrade(): Collection
    {
        return Subscription::with(['tenant', 'price.plan', 'nextPrice.plan'])
            ->whereNotNull('next_plan_price_id')
            ->where('ends_at', '<=', now())
            ->get();
    }

    /**
     * Get subscriptions with trials ending in the specified number of days.
     *
     * @param int $daysAhead Number of days ahead to check
     * @return Collection Collection of subscriptions with ending trials
     */
    public function getTrialEndingSubscriptions(int $daysAhead): Collection
    {
        $targetDate = now()->addDays($daysAhead)->startOfDay();

        return Subscription::with(['tenant.owner', 'price.plan'])
            ->whereDate('trial_ends_at', $targetDate)
            ->get();
    }

    /**
     * Process a scheduled downgrade for a subscription.
     *
     * @param Subscription $subscription The subscription to downgrade
     * @return void
     */
    public function processScheduledDowngrade(Subscription $subscription): void
    {
        $newPlanPriceId = $subscription->next_plan_price_id;
        $newPlanPrice = $subscription->nextPrice;

        $newEndsAt = $this->calculateNewEndDate($newPlanPrice);

        $subscription->update([
            'plan_price_id' => $newPlanPriceId,
            'next_plan_price_id' => null,
            'starts_at' => now(),
            'ends_at' => $newEndsAt,
        ]);
    }

    /**
     * Calculate the new end date based on plan price interval.
     *
     * @param PlanPrice $planPrice The plan price to calculate from
     * @return Carbon The calculated end date
     */
    private function calculateNewEndDate(PlanPrice $planPrice): Carbon
    {
        $now = now();

        return match ($planPrice->interval->value) {
            'day' => $now->addDays($planPrice->interval_count),
            'month' => $now->addMonths($planPrice->interval_count),
            'year' => $now->addYears($planPrice->interval_count),
            default => $now->addMonth(),
        };
    }
}
