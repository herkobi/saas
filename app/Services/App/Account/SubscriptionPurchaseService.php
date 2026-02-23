<?php

/**
 * Subscription Purchase Service
 *
 * This service handles subscription creation and renewal operations
 * after successful payment processing.
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

use App\Enums\CheckoutType;
use App\Enums\PlanInterval;
use App\Events\TenantSubscriptionPurchased;
use App\Events\TenantSubscriptionRenewed;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling subscription purchases.
 *
 * Provides methods for creating new subscriptions and renewing
 * existing ones after successful payment processing.
 */
class SubscriptionPurchaseService
{
    /**
     * Process a successful checkout and create/update subscription.
     *
     * @param Checkout $checkout The completed checkout
     * @param Payment $payment The associated payment
     * @return Subscription
     */
    public function processCheckout(Checkout $checkout, Payment $payment): Subscription
    {
        return match ($checkout->type) {
            CheckoutType::RENEW => $this->renewSubscription($checkout, $payment),
            default => $this->createSubscription($checkout, $payment),
        };
    }

    /**
     * Create a new subscription for a tenant.
     *
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     * @return Subscription
     */
    public function createSubscription(Checkout $checkout, Payment $payment): Subscription
    {
        return DB::transaction(function () use ($checkout, $payment) {
            $checkout->load('planPrice');

            $startsAt = now();
            $endsAt = $this->calculateEndDate($startsAt, $checkout->planPrice);

            $trialDays = $checkout->planPrice->trial_days ?? 0;
            $trialEndsAt = $trialDays > 0 ? $startsAt->copy()->addDays($trialDays) : null;

            $subscription = Subscription::create([
                'tenant_id' => $checkout->tenant_id,
                'plan_price_id' => $checkout->plan_price_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
            ]);

            $payment->update(['subscription_id' => $subscription->id]);

            TenantSubscriptionPurchased::dispatch($subscription, $checkout, $payment);

            return $subscription->load('price.plan');
        });
    }

    /**
     * Renew an existing subscription.
     *
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     * @return Subscription
     */
    public function renewSubscription(Checkout $checkout, Payment $payment): Subscription
    {
        return DB::transaction(function () use ($checkout, $payment) {
            $checkout->load(['tenant.subscription', 'planPrice']);

            $subscription = $checkout->tenant->subscription;

            if (!$subscription) {
                return $this->createSubscription($checkout, $payment);
            }

            $startsAt = $subscription->ends_at && $subscription->ends_at->isFuture()
                ? $subscription->ends_at
                : now();

            $endsAt = $this->calculateEndDate($startsAt, $checkout->planPrice);

            $subscription->update([
                'ends_at' => $endsAt,
                'canceled_at' => null,
                'grace_period_ends_at' => null,
            ]);

            $payment->update(['subscription_id' => $subscription->id]);

            TenantSubscriptionRenewed::dispatch($subscription, $checkout, $payment);

            return $subscription->fresh('price.plan');
        });
    }

    /**
     * Calculate the subscription end date based on plan price interval.
     *
     * @param Carbon $startDate The start date
     * @param PlanPrice $planPrice The plan price
     * @return Carbon
     */
    public function calculateEndDate(Carbon $startDate, PlanPrice $planPrice): Carbon
    {
        $interval = $planPrice->interval;
        $count = $planPrice->interval_count;

        return match ($interval) {
            PlanInterval::DAY => $startDate->copy()->addDays($count),
            PlanInterval::MONTH => $startDate->copy()->addMonths($count),
            PlanInterval::YEAR => $startDate->copy()->addYears($count),
            default => $startDate->copy()->addMonth(),
        };
    }
}
