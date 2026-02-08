<?php

/**
 * Plan Change Service Interface Contract
 *
 * This interface defines the contract for plan upgrade/downgrade operations.
 * It handles plan changes with proration calculations and scheduling.
 *
 * @package    App\Contracts\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Enums\ProrationType;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * Interface for plan change service implementations.
 *
 * Defines the contract for handling subscription plan changes
 * including upgrades with proration and downgrades at period end.
 */
interface PlanChangeServiceInterface
{
    /**
     * Get available plans for upgrade.
     *
     * @param Tenant $tenant The tenant
     * @return Collection
     */
    public function getUpgradeOptions(Tenant $tenant): Collection;

    /**
     * Get available plans for downgrade.
     *
     * @param Tenant $tenant The tenant
     * @return Collection
     */
    public function getDowngradeOptions(Tenant $tenant): Collection;

    /**
     * Calculate the proration amount for an upgrade.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return array{credit: float, new_amount: float, final_amount: float, days_remaining: int}
     */
    public function calculateUpgradeProration(Subscription $subscription, PlanPrice $newPlanPrice): array;

    /**
     * Process an upgrade after successful payment.
     *
     * @param Checkout $checkout The completed checkout
     * @param Payment $payment The associated payment
     * @return Subscription
     */
    public function processUpgrade(Checkout $checkout, Payment $payment): Subscription;

    /**
     * Calculate the proration amount for a downgrade (IMMEDIATE type).
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return array{credit: float, new_amount: float, final_amount: float, days_remaining: int}
     */
    public function calculateDowngradeProration(Subscription $subscription, PlanPrice $newPlanPrice): array;

    /**
     * Process an immediate downgrade after successful payment (or free downgrade).
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return Subscription
     */
    public function processImmediateDowngrade(Subscription $subscription, PlanPrice $newPlanPrice): Subscription;

    /**
     * Schedule a plan change for the end of the current period.
     * Works for both upgrades and downgrades with END_OF_PERIOD proration.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return Subscription
     */
    public function schedulePlanChange(Subscription $subscription, PlanPrice $newPlanPrice): Subscription;

    /**
     * Schedule a downgrade for the end of the current period.
     *
     * @param Subscription $subscription The current subscription
     * @param PlanPrice $newPlanPrice The new plan price
     * @return Subscription
     */
    public function scheduleDowngrade(Subscription $subscription, PlanPrice $newPlanPrice): Subscription;

    /**
     * Cancel a scheduled downgrade.
     *
     * @param Subscription $subscription The subscription with scheduled downgrade
     * @return Subscription
     */
    public function cancelScheduledDowngrade(Subscription $subscription): Subscription;

    /**
     * Check if a plan change is an upgrade.
     *
     * @param PlanPrice $currentPrice The current plan price
     * @param PlanPrice $newPrice The new plan price
     * @return bool
     */
    public function isUpgrade(PlanPrice $currentPrice, PlanPrice $newPrice): bool;

    /**
     * Resolve the proration type for a plan change.
     *
     * @param PlanPrice $currentPrice The current plan price
     * @param PlanPrice $newPrice The new plan price
     * @return ProrationType
     */
    public function resolveProrationType(PlanPrice $currentPrice, PlanPrice $newPrice): ProrationType;

    /**
     * Apply scheduled plan changes that are due.
     *
     * @return int Number of changes applied
     */
    public function applyScheduledDowngrades(): int;
}
