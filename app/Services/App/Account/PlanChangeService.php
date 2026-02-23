<?php

/**
 * Plan Change Service
 *
 * This service handles plan upgrade and downgrade operations
 * including proration calculations and scheduling.
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

use App\Services\App\Account\ProrationService;
use App\Enums\ProrationType;
use App\Events\TenantSubscriptionDowngraded;
use App\Events\TenantSubscriptionUpgraded;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling plan changes.
 *
 * Provides methods for calculating proration, processing upgrades,
 * and scheduling downgrades at period end.
 */
class PlanChangeService
{
    /**
     * Create a new plan change service instance.
     *
     * @param ProrationService $prorationService The proration service
     */
    public function __construct(
        protected ProrationService $prorationService
    ) {}

    /**
     * Get available plans for upgrade.
     *
     * @param Tenant $tenant The tenant
     * @return Collection
     */
    public function getUpgradeOptions(Tenant $tenant): Collection
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return collect();
        }

        $currentPrice = (float) $subscription->price->price;
        $currentInterval = $subscription->price->interval;

        return PlanPrice::with('plan')
            ->whereHas('plan', fn($q) => $q->where('is_active', true)->where('is_public', true))
            ->where('interval', $currentInterval)
            ->where('price', '>', $currentPrice)
            ->get()
            ->map(function (PlanPrice $planPrice) use ($subscription) {
                $prorationType = $planPrice->plan->resolveUpgradeProration();
                $proration = $this->prorationService->calculate($subscription, $planPrice, $prorationType);

                return [
                    'plan_price' => $planPrice,
                    'plan' => $planPrice->plan,
                    'proration' => $proration,
                    'proration_type' => $prorationType,
                ];
            });
    }

    /**
     * Get available plans for downgrade.
     *
     * @param Tenant $tenant The tenant
     * @return Collection
     */
    public function getDowngradeOptions(Tenant $tenant): Collection
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return collect();
        }

        $currentPrice = (float) $subscription->price->price;
        $currentInterval = $subscription->price->interval;

        return PlanPrice::with('plan')
            ->whereHas('plan', fn($q) => $q->where('is_active', true)->where('is_public', true))
            ->where('interval', $currentInterval)
            ->where('price', '<', $currentPrice)
            ->get()
            ->map(function (PlanPrice $planPrice) use ($subscription) {
                $prorationType = $planPrice->plan->resolveDowngradeProration();
                $proration = $this->prorationService->calculate($subscription, $planPrice, $prorationType);

                return [
                    'plan_price' => $planPrice,
                    'plan' => $planPrice->plan,
                    'proration' => $proration,
                    'proration_type' => $prorationType,
                    'effective_at' => $prorationType->isImmediate() ? now() : $subscription->ends_at,
                ];
            });
    }

    /**
     * Calculate the proration amount for an upgrade.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return array{credit: float, new_amount: float, final_amount: float, days_remaining: int}
     */
    public function calculateUpgradeProration(Subscription $subscription, PlanPrice $newPlanPrice): array
    {
        $newPlanPrice->loadMissing('plan');
        $prorationType = $newPlanPrice->plan->resolveUpgradeProration();
        $proration = $this->prorationService->calculate($subscription, $newPlanPrice, $prorationType);

        return [
            'credit' => $proration['credit'],
            'new_amount' => $proration['new_amount'],
            'final_amount' => $proration['final_amount'],
            'days_remaining' => $proration['days_remaining'],
            'proration_type' => $prorationType,
        ];
    }

    public function calculateDowngradeProration(Subscription $subscription, PlanPrice $newPlanPrice): array
    {
        $newPlanPrice->loadMissing('plan');
        $prorationType = $newPlanPrice->plan->resolveDowngradeProration();
        $proration = $this->prorationService->calculate($subscription, $newPlanPrice, $prorationType);

        return [
            'credit' => $proration['credit'],
            'new_amount' => $proration['new_amount'],
            'final_amount' => $proration['final_amount'],
            'days_remaining' => $proration['days_remaining'],
            'proration_type' => $prorationType,
        ];
    }

    /**
     * Process an upgrade after successful payment.
     *
     * @param Checkout $checkout The completed checkout
     * @param Payment $payment The associated payment
     * @return Subscription
     */
    public function processUpgrade(Checkout $checkout, Payment $payment): Subscription
    {
        return DB::transaction(function () use ($checkout, $payment) {
            $checkout->load(['tenant', 'planPrice']);

            $subscription = Subscription::where('tenant_id', $checkout->tenant_id)
                ->lockForUpdate()
                ->latest()
                ->first();

            if (!$subscription) {
                throw new \RuntimeException('Subscription not found for tenant: ' . $checkout->tenant_id);
            }

            $oldPlanPriceId = $subscription->plan_price_id;

            $subscription->update([
                'plan_price_id' => $checkout->plan_price_id,
                'next_plan_price_id' => null,
            ]);

            $payment->update(['subscription_id' => $subscription->id]);

            TenantSubscriptionUpgraded::dispatch(
                $subscription,
                $oldPlanPriceId,
                $checkout->plan_price_id,
                $checkout,
                $payment
            );

            return $subscription->fresh('price.plan');
        });
    }

    public function processImmediateDowngrade(Subscription $subscription, PlanPrice $newPlanPrice): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlanPrice) {
            $oldPlanPriceId = $subscription->plan_price_id;

            $subscription->update([
                'plan_price_id' => $newPlanPrice->id,
                'next_plan_price_id' => null,
            ]);

            TenantSubscriptionDowngraded::dispatch(
                $subscription->fresh(),
                $oldPlanPriceId,
                $newPlanPrice->id,
                true
            );

            return $subscription->fresh('price.plan');
        });
    }

    public function schedulePlanChange(Subscription $subscription, PlanPrice $newPlanPrice): Subscription
    {
        $subscription->update([
            'next_plan_price_id' => $newPlanPrice->id,
        ]);

        return $subscription->fresh(['price.plan', 'nextPrice.plan']);
    }

    /**
     * Schedule a downgrade for the end of the current period.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return Subscription
     */
    public function scheduleDowngrade(Subscription $subscription, PlanPrice $newPlanPrice): Subscription
    {
        return $this->schedulePlanChange($subscription, $newPlanPrice);
    }

    /**
     * Cancel a scheduled downgrade.
     *
     * @param Subscription $subscription The subscription with scheduled downgrade
     * @return Subscription
     */
    public function cancelScheduledDowngrade(Subscription $subscription): Subscription
    {
        $subscription->update([
            'next_plan_price_id' => null,
        ]);

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Check if a plan change is an upgrade.
     *
     * @param PlanPrice $currentPrice The current plan price
     * @param PlanPrice $newPrice The new plan price
     * @return bool
     */
    public function isUpgrade(PlanPrice $currentPrice, PlanPrice $newPrice): bool
    {
        return (float) $newPrice->price > (float) $currentPrice->price;
    }

    public function resolveProrationType(PlanPrice $currentPrice, PlanPrice $newPrice): ProrationType
    {
        $newPrice->loadMissing('plan');

        if ($this->isUpgrade($currentPrice, $newPrice)) {
            return $newPrice->plan->resolveUpgradeProration();
        }

        return $newPrice->plan->resolveDowngradeProration();
    }

    /**
     * Apply scheduled plan changes that are due.
     *
     * @return int Number of changes applied
     */
    public function applyScheduledDowngrades(): int
    {
        $count = 0;

        $subscriptions = Subscription::whereNotNull('next_plan_price_id')
            ->where('ends_at', '<=', now())
            ->with(['price.plan', 'nextPrice.plan'])
            ->get();

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            try {
                DB::transaction(function () use ($subscription, &$count) {
                    $oldPlanPriceId = $subscription->plan_price_id;
                    $newPlanPriceId = $subscription->next_plan_price_id;
                    $isUpgrade = $this->isUpgrade($subscription->price, $subscription->nextPrice);

                    $subscription->update([
                        'plan_price_id' => $newPlanPriceId,
                        'next_plan_price_id' => null,
                    ]);

                    $freshSubscription = $subscription->fresh();

                    if ($isUpgrade) {
                        TenantSubscriptionUpgraded::dispatch(
                            $freshSubscription,
                            $oldPlanPriceId,
                            $newPlanPriceId
                        );
                    } else {
                        TenantSubscriptionDowngraded::dispatch(
                            $freshSubscription,
                            $oldPlanPriceId,
                            $newPlanPriceId,
                            true
                        );
                    }

                    $count++;
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $count;
    }
}
