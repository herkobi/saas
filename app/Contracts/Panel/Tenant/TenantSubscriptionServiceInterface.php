<?php

/**
 * Panel Tenant Subscription Service Interface Contract
 *
 * This interface defines the contract for panel tenant subscription
 * service implementations, providing methods for subscription management
 * within the panel domain.
 *
 * @package    App\Contracts\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Panel\Tenant;

use App\Enums\SubscriptionStatus;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Interface for panel tenant subscription service implementations.
 *
 * Defines the contract for managing tenant subscriptions from the panel,
 * including create, cancel, renew, and plan change operations with audit context.
 */
interface TenantSubscriptionServiceInterface
{
    /**
     * Get the current active subscription for a tenant.
     *
     * @param Tenant $tenant The tenant to get subscription for
     * @return Subscription|null The current subscription or null
     */
    public function getCurrentSubscription(Tenant $tenant): ?Subscription;

    /**
     * Get subscription history for a tenant.
     *
     * @param Tenant $tenant The tenant to get history for
     * @return Collection Collection of past subscriptions
     */
    public function getSubscriptionHistory(Tenant $tenant): Collection;

    /**
     * Create a new subscription for a tenant.
     *
     * @param Tenant $tenant The tenant to create subscription for
     * @param PlanPrice $planPrice The plan price to subscribe to
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @param array<string, mixed>|null $options Additional options (trial_days, etc.)
     * @return Subscription The created subscription
     */
    public function create(
        Tenant $tenant,
        PlanPrice $planPrice,
        User $admin,
        string $ipAddress,
        string $userAgent,
        ?array $options = []
    ): Subscription;

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
    ): Subscription;

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
    ): Subscription;

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
    ): Subscription;

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
    ): Subscription;

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
    ): Subscription;

    /**
     * Manually update the status of a subscription.
     *
     * This is an admin override for exceptional cases (manual payments, customer agreements, etc.).
     * Should be used cautiously as it bypasses normal business logic.
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
    ): Subscription;
}
