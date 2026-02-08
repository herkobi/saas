<?php

/**
 * Notify Tenant On Subscription Change Listener
 *
 * This listener handles sending notifications to tenant owners
 * when subscription changes are made by admin users.
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

use App\Events\PanelTenantSubscriptionCancelled;
use App\Events\PanelTenantSubscriptionPlanChanged;
use App\Events\PanelTenantSubscriptionRenewed;
use App\Notifications\Panel\Tenant\SubscriptionCancelledNotification;
use App\Notifications\Panel\Tenant\SubscriptionPlanChangedNotification;
use App\Notifications\Panel\Tenant\SubscriptionRenewedNotification;

/**
 * Listener for notifying tenants about subscription changes.
 *
 * Sends appropriate notifications to tenant owners when their
 * subscription is cancelled, renewed, or plan is changed by admin.
 */
class SendPanelTenantSubscription
{
    /**
     * Handle the subscription cancelled by admin event.
     *
     * @param PanelTenantSubscriptionCancelled $event The subscription cancelled event
     * @return void
     */
    public function handleSubscriptionCancelled(PanelTenantSubscriptionCancelled $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new SubscriptionCancelledNotification(
                $event->subscription,
                $event->immediate
            ));
        }
    }

    /**
     * Handle the subscription renewed by admin event.
     *
     * @param PanelTenantSubscriptionRenewed $event The subscription renewed event
     * @return void
     */
    public function handleSubscriptionRenewed(PanelTenantSubscriptionRenewed $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new SubscriptionRenewedNotification(
                $event->subscription
            ));
        }
    }

    /**
     * Handle the subscription plan changed by admin event.
     *
     * @param PanelTenantSubscriptionPlanChanged $event The subscription plan changed event
     * @return void
     */
    public function handleSubscriptionPlanChanged(PanelTenantSubscriptionPlanChanged $event): void
    {
        $tenant = $event->subscription->tenant;
        $owner = $tenant->owner();

        if ($owner) {
            $owner->notify(new SubscriptionPlanChangedNotification(
                $event->subscription,
                $event->oldPlanPriceId,
                $event->newPlanPriceId,
                $event->immediate
            ));
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events The event dispatcher
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            PanelTenantSubscriptionCancelled::class => 'handleSubscriptionCancelled',
            PanelTenantSubscriptionRenewed::class => 'handleSubscriptionRenewed',
            PanelTenantSubscriptionPlanChanged::class => 'handleSubscriptionPlanChanged',
        ];
    }
}
