<?php

/**
 * Send Payment Notifications Listener
 *
 * This listener sends notifications to users after payment events.
 *
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 *
 * @version    1.0.0
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TenantAddonPurchased;
use App\Events\TenantPaymentFailed;
use App\Events\TenantPaymentSucceeded;
use App\Events\TenantSubscriptionDowngraded;
use App\Events\TenantSubscriptionPurchased;
use App\Events\TenantSubscriptionRenewed;
use App\Events\TenantSubscriptionUpgraded;
use App\Notifications\App\Account\AddonPurchasedNotification;
use App\Notifications\App\Account\PaymentFailedNotification;
use App\Notifications\App\Account\PaymentSucceededNotification;
use App\Notifications\App\Account\PlanDowngradedNotification;
use App\Notifications\App\Account\PlanUpgradedNotification;
use App\Notifications\App\Account\SubscriptionActivatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

/**
 * Listener for sending payment-related notifications.
 *
 * Notifies tenant owners about payment successes, failures, and subscription changes.
 */
class SendTenantPaymentNotifications implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle payment succeeded event.
     */
    public function handlePaymentSucceeded(TenantPaymentSucceeded $event): void
    {
        $tenant = $event->checkout->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new PaymentSucceededNotification(
                $event->payment,
                $event->checkout
            ));
        }
    }

    /**
     * Handle payment failed event.
     */
    public function handlePaymentFailed(TenantPaymentFailed $event): void
    {
        $tenant = $event->checkout->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new PaymentFailedNotification(
                $event->checkout
            ));
        }
    }

    /**
     * Handle subscription purchased event.
     */
    public function handleSubscriptionPurchased(TenantSubscriptionPurchased $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new SubscriptionActivatedNotification(
                $event->subscription
            ));
        }
    }

    /**
     * Handle subscription upgraded event.
     */
    public function handleSubscriptionUpgraded(TenantSubscriptionUpgraded $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new PlanUpgradedNotification(
                $event->subscription,
                $event->oldPlanPriceId,
                $event->newPlanPriceId
            ));
        }
    }

    /**
     * Handle subscription downgraded event.
     */
    public function handleSubscriptionDowngraded(TenantSubscriptionDowngraded $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new PlanDowngradedNotification(
                $event->subscription,
                $event->oldPlanPriceId,
                $event->newPlanPriceId,
                $event->immediate
            ));
        }
    }

    /**
     * Handle subscription renewed event.
     */
    public function handleSubscriptionRenewed(TenantSubscriptionRenewed $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new PaymentSucceededNotification(
                $event->payment,
                $event->checkout
            ));
        }
    }

    /**
     * Handle addon purchased event.
     */
    public function handleAddonPurchased(TenantAddonPurchased $event): void
    {
        $owner = $event->tenant->owner();

        if ($owner) {
            $owner->notify(new AddonPurchasedNotification(
                $event->tenantAddon->addon,
                $event->tenantAddon
            ));
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantPaymentSucceeded::class => 'handlePaymentSucceeded',
            TenantPaymentFailed::class => 'handlePaymentFailed',
            TenantSubscriptionPurchased::class => 'handleSubscriptionPurchased',
            TenantSubscriptionUpgraded::class => 'handleSubscriptionUpgraded',
            TenantSubscriptionDowngraded::class => 'handleSubscriptionDowngraded',
            TenantSubscriptionRenewed::class => 'handleSubscriptionRenewed',
            TenantAddonPurchased::class => 'handleAddonPurchased',
        ];
    }
}
