<?php

/**
 * Panel Tenant Subscription Service
 *
 * This service handles tenant subscription management operations for the panel,
 * including create, cancel, renew, and plan change operations.
 *
 * @package    App\Services\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Tenant;

use App\Enums\SubscriptionStatus;
use App\Events\PanelTenantGracePeriodExtended;
use App\Events\PanelTenantSubscriptionCancelled;
use App\Events\PanelTenantSubscriptionCreated;
use App\Events\PanelTenantSubscriptionPlanChanged;
use App\Events\PanelTenantSubscriptionRenewed;
use App\Events\PanelTenantSubscriptionStatusUpdated;
use App\Events\PanelTenantTrialExtended;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service for managing tenant subscriptions from the panel.
 *
 * Provides methods for subscription CRUD operations with comprehensive
 * audit logging and event dispatching.
 */
class TenantSubscriptionService
{
    /**
     * Get the current active subscription for a tenant.
     *
     * @param Tenant $tenant The tenant to get subscription for
     * @return Subscription|null The current subscription or null
     */
    public function getCurrentSubscription(Tenant $tenant): ?Subscription
    {
        return $tenant->subscription()
            ->with(['price.plan', 'nextPrice.plan'])
            ->first();
    }

    /**
     * Get subscription history for a tenant.
     *
     * @param Tenant $tenant The tenant to get history for
     * @return Collection Collection of past subscriptions
     */
    public function getSubscriptionHistory(Tenant $tenant): Collection
    {
        return $tenant->subscriptions()
            ->with(['price.plan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new subscription for a tenant.
     *
     * @param Tenant $tenant The tenant to create subscription for
     * @param PlanPrice $planPrice The plan price to subscribe to
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @param array<string, mixed>|null $options Additional options
     * @return Subscription The created subscription
     */
    public function create(
        Tenant $tenant,
        PlanPrice $planPrice,
        User $admin,
        string $ipAddress,
        string $userAgent,
        ?array $options = []
    ): Subscription {
        $startsAt = now();
        $endsAt = $this->calculateEndDate($startsAt, $planPrice);

        $trialDays = $options['trial_days'] ?? $planPrice->trial_days;
        $trialEndsAt = $trialDays > 0 ? $startsAt->copy()->addDays($trialDays) : null;

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $planPrice->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
        ]);

        PanelTenantSubscriptionCreated::dispatch(
            $subscription,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->load(['price.plan']);
    }

    /**
     * Change the subscription plan for a tenant.
     *
     * @param Subscription $subscription The subscription to change
     * @param PlanPrice $newPlanPrice The new plan price
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @param bool $immediate Whether to apply immediately or at period end
     * @return Subscription The updated subscription
     */
    public function changePlan(
        Subscription $subscription,
        PlanPrice $newPlanPrice,
        User $admin,
        string $ipAddress,
        string $userAgent,
        bool $immediate = false
    ): Subscription {
        $oldPlanPriceId = $subscription->plan_price_id;

        if ($immediate) {
            $subscription->update([
                'plan_price_id' => $newPlanPrice->id,
                'next_plan_price_id' => null,
                'ends_at' => $this->calculateEndDate(now(), $newPlanPrice),
            ]);
        } else {
            $subscription->update([
                'next_plan_price_id' => $newPlanPrice->id,
            ]);
        }

        PanelTenantSubscriptionPlanChanged::dispatch(
            $subscription,
            $oldPlanPriceId,
            $newPlanPrice->id,
            $immediate,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan', 'nextPrice.plan']);
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription The subscription to cancel
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @param bool $immediate Whether to cancel immediately or at period end
     * @return Subscription The cancelled subscription
     */
    public function cancel(
        Subscription $subscription,
        User $admin,
        string $ipAddress,
        string $userAgent,
        bool $immediate = false
    ): Subscription {
        $canceledAt = now();

        if ($immediate) {
            $subscription->update([
                'canceled_at' => $canceledAt,
                'ends_at' => $canceledAt,
                'grace_period_ends_at' => null,
            ]);
        } else {
            $subscription->update([
                'canceled_at' => $canceledAt,
            ]);
        }

        PanelTenantSubscriptionCancelled::dispatch(
            $subscription,
            $immediate,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Renew a subscription.
     *
     * @param Subscription $subscription The subscription to renew
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Subscription The renewed subscription
     */
    public function renew(
        Subscription $subscription,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): Subscription {
        $planPrice = $subscription->nextPrice ?? $subscription->price;
        $startsAt = $subscription->ends_at ?? now();
        $endsAt = $this->calculateEndDate($startsAt, $planPrice);

        $subscription->update([
            'plan_price_id' => $planPrice->id,
            'next_plan_price_id' => null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'canceled_at' => null,
            'grace_period_ends_at' => null,
        ]);

        PanelTenantSubscriptionRenewed::dispatch(
            $subscription,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Extend the trial period for a subscription.
     *
     * @param Subscription $subscription The subscription to extend trial for
     * @param int $days Number of days to extend
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Subscription The updated subscription
     */
    public function extendTrial(
        Subscription $subscription,
        int $days,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): Subscription {
        $currentTrialEnd = $subscription->trial_ends_at ?? now();
        $newTrialEnd = $currentTrialEnd->copy()->addDays($days);

        $subscription->update([
            'trial_ends_at' => $newTrialEnd,
        ]);

        PanelTenantTrialExtended::dispatch(
            $subscription,
            $days,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Extend the grace period for a subscription.
     *
     * @param Subscription $subscription The subscription to extend grace period for
     * @param int $days Number of days to extend
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Subscription The updated subscription
     */
    public function extendGracePeriod(
        Subscription $subscription,
        int $days,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): Subscription {
        $currentGraceEnd = $subscription->grace_period_ends_at ?? now();
        $newGraceEnd = $currentGraceEnd->copy()->addDays($days);

        $subscription->update([
            'grace_period_ends_at' => $newGraceEnd,
        ]);

        PanelTenantGracePeriodExtended::dispatch(
            $subscription,
            $days,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Manually update the status of a subscription.
     *
     * This is an admin override for exceptional cases (manual payments, customer agreements, etc.).
     * Note: This directly sets the status field in the Subscription model.
     *
     * @param Subscription $subscription The subscription to update
     * @param SubscriptionStatus $status The new status
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @param string|null $reason Optional reason for the status change
     * @return Subscription The updated subscription
     */
    public function updateStatus(
        Subscription $subscription,
        SubscriptionStatus $status,
        User $admin,
        string $ipAddress,
        string $userAgent,
        ?string $reason = null
    ): Subscription {
        $oldStatus = $subscription->status;

        // Update the status directly (Subscription model has status accessor/mutator)
        $subscription->update([
            'status' => $status,
        ]);

        PanelTenantSubscriptionStatusUpdated::dispatch(
            $subscription,
            $oldStatus,
            $status,
            $reason,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Update the custom price for a subscription.
     */
    public function updateCustomPrice(
        Subscription $subscription,
        ?float $customPrice,
        ?string $customCurrency,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): Subscription {
        $oldPrice = $subscription->custom_price ? (float) $subscription->custom_price : null;

        $subscription->update([
            'custom_price' => $customPrice,
            'custom_currency' => $customCurrency,
        ]);

        \App\Events\PanelTenantSubscriptionCustomPriceUpdated::dispatch(
            $subscription,
            $oldPrice,
            $customPrice,
            $admin,
            $ipAddress,
            $userAgent
        );

        return $subscription->fresh(['price.plan']);
    }

    /**
     * Calculate the end date based on plan price interval.
     *
     * @param \Carbon\Carbon $startDate The start date
     * @param PlanPrice $planPrice The plan price
     * @return \Carbon\Carbon The calculated end date
     */
    private function calculateEndDate(\Carbon\Carbon $startDate, PlanPrice $planPrice): \Carbon\Carbon
    {
        $endDate = $startDate->copy();

        return match ($planPrice->interval->value) {
            'day' => $endDate->addDays($planPrice->interval_count),
            'week' => $endDate->addWeeks($planPrice->interval_count),
            'month' => $endDate->addMonths($planPrice->interval_count),
            'year' => $endDate->addYears($planPrice->interval_count),
            default => $endDate->addMonth(),
        };
    }
}
