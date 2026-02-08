<?php

/**
 * Log Payment Activity Listener
 *
 * This listener logs payment-related activities to the activity table.
 *
 * @package    App\Listeners
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\TenantCheckoutInitiated;
use App\Events\TenantPaymentFailed;
use App\Events\TenantPaymentSucceeded;
use App\Events\TenantSubscriptionDowngraded;
use App\Events\TenantSubscriptionPurchased;
use App\Events\TenantSubscriptionRenewed;
use App\Events\TenantSubscriptionUpgraded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

/**
 * Listener for logging payment activities.
 *
 * Records payment events to the activity log for audit purposes.
 */
class LogTenantPaymentActivity implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'default';

    /**
     * Create the event listener.
     *
     * @param ActivityServiceInterface $activityService Service for logging activities
     */
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle checkout initiated event.
     *
     * @param TenantCheckoutInitiated $event
     * @return void
     */
    public function handleCheckoutInitiated(TenantCheckoutInitiated $event): void
    {
        $checkout = $event->checkout;
        $owner = $checkout->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'payment.checkout_initiated',
                description: 'Ödeme işlemi başlatıldı',
                log: [
                    'checkout_id' => $checkout->id,
                    'merchant_oid' => $checkout->merchant_oid,
                    'checkout_type' => $checkout->type,
                    'amount' => $checkout->final_amount,
                ],
                tenantId: $checkout->tenant_id
            );
        }
    }

    /**
     * Handle payment succeeded event.
     *
     * @param TenantPaymentSucceeded $event
     * @return void
     */
    public function handlePaymentSucceeded(TenantPaymentSucceeded $event): void
    {
        $checkout = $event->checkout;
        $payment = $event->payment;
        $owner = $checkout->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'payment.succeeded',
                description: 'Ödeme başarıyla tamamlandı',
                log: [
                    'checkout_id' => $checkout->id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                ],
                tenantId: $checkout->tenant_id
            );
        }
    }

    /**
     * Handle payment failed event.
     *
     * @param TenantPaymentFailed $event
     * @return void
     */
    public function handlePaymentFailed(TenantPaymentFailed $event): void
    {
        $checkout = $event->checkout;
        $owner = $checkout->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'payment.failed',
                description: 'Ödeme başarısız oldu',
                log: [
                    'checkout_id' => $checkout->id,
                    'merchant_oid' => $checkout->merchant_oid,
                ],
                tenantId: $checkout->tenant_id
            );
        }
    }

    /**
     * Handle subscription purchased event.
     *
     * @param TenantSubscriptionPurchased $event
     * @return void
     */
    public function handleSubscriptionPurchased(TenantSubscriptionPurchased $event): void
    {
        $subscription = $event->subscription;
        $owner = $subscription->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.purchased',
                description: 'Yeni abonelik satın alındı',
                log: [
                    'subscription_id' => $subscription->id,
                    'plan_price_id' => $subscription->plan_price_id,
                ],
                tenantId: $subscription->tenant_id
            );
        }
    }

    /**
     * Handle subscription upgraded event.
     *
     * @param TenantSubscriptionUpgraded $event
     * @return void
     */
    public function handleSubscriptionUpgraded(TenantSubscriptionUpgraded $event): void
    {
        $subscription = $event->subscription;
        $owner = $subscription->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.upgraded',
                description: 'Abonelik planı yükseltildi',
                log: [
                    'subscription_id' => $subscription->id,
                    'old_plan_price_id' => $event->oldPlanPriceId,
                    'new_plan_price_id' => $event->newPlanPriceId,
                ],
                tenantId: $subscription->tenant_id
            );
        }
    }

    /**
     * Handle subscription downgraded event.
     *
     * @param TenantSubscriptionDowngraded $event
     * @return void
     */
    public function handleSubscriptionDowngraded(TenantSubscriptionDowngraded $event): void
    {
        $subscription = $event->subscription;
        $owner = $subscription->tenant->owner();

        $description = $event->immediate
            ? 'Abonelik planı düşürüldü'
            : 'Abonelik planı düşürme planlandı';

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.downgraded',
                description: $description,
                log: [
                    'subscription_id' => $subscription->id,
                    'old_plan_price_id' => $event->oldPlanPriceId,
                    'new_plan_price_id' => $event->newPlanPriceId,
                    'immediate' => $event->immediate,
                ],
                tenantId: $subscription->tenant_id
            );
        }
    }

    /**
     * Handle subscription renewed event.
     *
     * @param TenantSubscriptionRenewed $event
     * @return void
     */
    public function handleSubscriptionRenewed(TenantSubscriptionRenewed $event): void
    {
        $subscription = $event->subscription;
        $owner = $subscription->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.renewed',
                description: 'Abonelik yenilendi',
                log: [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $event->payment->id,
                ],
                tenantId: $subscription->tenant_id
            );
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantCheckoutInitiated::class => 'handleCheckoutInitiated',
            TenantPaymentSucceeded::class => 'handlePaymentSucceeded',
            TenantPaymentFailed::class => 'handlePaymentFailed',
            TenantSubscriptionPurchased::class => 'handleSubscriptionPurchased',
            TenantSubscriptionUpgraded::class => 'handleSubscriptionUpgraded',
            TenantSubscriptionDowngraded::class => 'handleSubscriptionDowngraded',
            TenantSubscriptionRenewed::class => 'handleSubscriptionRenewed',
        ];
    }
}
