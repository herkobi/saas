<?php

/**
 * Subscription Lifecycle Service Interface
 *
 * This interface defines the contract for subscription lifecycle operations.
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

use App\Models\Subscription;
use Illuminate\Support\Collection;

/**
 * Interface for subscription lifecycle service implementations.
 */
interface SubscriptionLifecycleServiceInterface
{
    /**
     * Get subscriptions expiring in the specified number of days.
     *
     * @param int $daysAhead Number of days ahead to check
     * @return Collection
     */
    public function getExpiringSubscriptions(int $daysAhead): Collection;

    /**
     * Get subscriptions with scheduled downgrades ready to process.
     *
     * @return Collection
     */
    public function getSubscriptionsWithScheduledDowngrade(): Collection;

    /**
     * Get subscriptions with trials ending in the specified number of days.
     *
     * @param int $daysAhead Number of days ahead to check
     * @return Collection
     */
    public function getTrialEndingSubscriptions(int $daysAhead): Collection;

    /**
     * Process a scheduled downgrade for a subscription.
     *
     * @param Subscription $subscription The subscription to downgrade
     * @return void
     */
    public function processScheduledDowngrade(Subscription $subscription): void;
}
