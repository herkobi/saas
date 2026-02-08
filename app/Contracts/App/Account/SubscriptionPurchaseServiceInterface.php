<?php

/**
 * Subscription Purchase Service Interface Contract
 *
 * This interface defines the contract for subscription purchase operations.
 * It handles new subscriptions, renewals, and related subscription management.
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

use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Subscription;

/**
 * Interface for subscription purchase service implementations.
 *
 * Defines the contract for creating and renewing subscriptions
 * after successful payment processing.
 */
interface SubscriptionPurchaseServiceInterface
{
    /**
     * Process a successful checkout and create/update subscription.
     *
     * @param Checkout $checkout The completed checkout
     * @param Payment $payment The associated payment
     * @return Subscription
     */
    public function processCheckout(Checkout $checkout, Payment $payment): Subscription;

    /**
     * Create a new subscription for a tenant.
     *
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     * @return Subscription
     */
    public function createSubscription(Checkout $checkout, Payment $payment): Subscription;

    /**
     * Renew an existing subscription.
     *
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     * @return Subscription
     */
    public function renewSubscription(Checkout $checkout, Payment $payment): Subscription;

    /**
     * Calculate the subscription end date based on plan price interval.
     *
     * @param \Carbon\Carbon $startDate The start date
     * @param \App\Models\PlanPrice $planPrice The plan price
     * @return \Carbon\Carbon
     */
    public function calculateEndDate(\Carbon\Carbon $startDate, \App\Models\PlanPrice $planPrice): \Carbon\Carbon;
}
