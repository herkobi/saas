<?php

/**
 * Log Admin Subscription Activity Listener
 *
 * This listener handles the logging of admin subscription management activities.
 * It records subscription create, cancel, renew, and plan change operations
 * performed by admin users for auditing purposes.
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
use App\Events\PanelTenantGracePeriodExtended;
use App\Events\PanelTenantSubscriptionCancelled;
use App\Events\PanelTenantSubscriptionCreated;
use App\Events\PanelTenantSubscriptionPlanChanged;
use App\Events\PanelTenantSubscriptionRenewed;
use App\Events\PanelTenantSubscriptionStatusUpdated;
use App\Events\PanelTenantTrialExtended;

/**
 * Listener for logging admin subscription activities.
 *
 * Records subscription management events with comprehensive audit information
 * for tracking and compliance purposes.
 */
class LogPanelTenantSubscriptionActivity
{
    /**
     * Create the event listener.
     *
     * @param ActivityServiceInterface $activityService Service for logging activities
     */
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle the subscription created by admin event.
     *
     * @param PanelTenantSubscriptionCreated $event The subscription created event
     * @return void
     */
    public function handleSubscriptionCreated(PanelTenantSubscriptionCreated $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.created',
            description: 'Admin kullanıcı yeni abonelik oluşturdu',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_price_id' => $subscription->plan_price_id,
                'plan_name' => $subscription->price?->plan?->name,
                'starts_at' => $subscription->starts_at?->toDateTimeString(),
                'ends_at' => $subscription->ends_at?->toDateTimeString(),
                'trial_ends_at' => $subscription->trial_ends_at?->toDateTimeString(),
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'created_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the subscription cancelled by admin event.
     *
     * @param PanelTenantSubscriptionCancelled $event The subscription cancelled event
     * @return void
     */
    public function handleSubscriptionCancelled(PanelTenantSubscriptionCancelled $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.cancelled',
            description: 'Admin kullanıcı aboneliği iptal etti',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->price?->plan?->name,
                'immediate' => $event->immediate,
                'canceled_at' => $subscription->canceled_at?->toDateTimeString(),
                'ends_at' => $subscription->ends_at?->toDateTimeString(),
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'cancelled_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the subscription renewed by admin event.
     *
     * @param PanelTenantSubscriptionRenewed $event The subscription renewed event
     * @return void
     */
    public function handleSubscriptionRenewed(PanelTenantSubscriptionRenewed $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.renewed',
            description: 'Admin kullanıcı aboneliği yeniledi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->price?->plan?->name,
                'starts_at' => $subscription->starts_at?->toDateTimeString(),
                'ends_at' => $subscription->ends_at?->toDateTimeString(),
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'renewed_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the subscription plan changed by admin event.
     *
     * @param PanelTenantSubscriptionPlanChanged $event The subscription plan changed event
     * @return void
     */
    public function handleSubscriptionPlanChanged(PanelTenantSubscriptionPlanChanged $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.plan_changed',
            description: 'Admin kullanıcı abonelik planını değiştirdi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'old_plan_price_id' => $event->oldPlanPriceId,
                'new_plan_price_id' => $event->newPlanPriceId,
                'immediate' => $event->immediate,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'changed_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleTrialExtended(PanelTenantTrialExtended $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.trial_extended',
            description: 'Admin kullanıcı deneme süresini uzattı',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->price?->plan?->name,
                'extended_days' => $event->days,
                'new_trial_ends_at' => $subscription->trial_ends_at?->toDateTimeString(),
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'extended_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleGracePeriodExtended(PanelTenantGracePeriodExtended $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.grace_period_extended',
            description: 'Admin kullanıcı ek süreyi uzattı',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->price?->plan?->name,
                'extended_days' => $event->days,
                'new_grace_period_ends_at' => $subscription->grace_period_ends_at?->toDateTimeString(),
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'extended_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the subscription status updated by admin event.
     *
     * @param PanelTenantSubscriptionStatusUpdated $event The subscription status updated event
     * @return void
     */
    public function handleStatusUpdated(PanelTenantSubscriptionStatusUpdated $event): void
    {
        $subscription = $event->subscription;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.subscription.status_updated',
            description: 'Admin kullanıcı abonelik durumunu manuel olarak değiştirdi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->price?->plan?->name,
                'old_status' => $event->oldStatus->value,
                'old_status_label' => $event->oldStatus->label(),
                'new_status' => $event->newStatus->value,
                'new_status_label' => $event->newStatus->label(),
                'reason' => $event->reason,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
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
            PanelTenantSubscriptionCreated::class => 'handleSubscriptionCreated',
            PanelTenantSubscriptionCancelled::class => 'handleSubscriptionCancelled',
            PanelTenantSubscriptionRenewed::class => 'handleSubscriptionRenewed',
            PanelTenantSubscriptionPlanChanged::class => 'handleSubscriptionPlanChanged',
            PanelTenantTrialExtended::class => 'handleTrialExtended',
            PanelTenantGracePeriodExtended::class => 'handleGracePeriodExtended',
            PanelTenantSubscriptionStatusUpdated::class => 'handleStatusUpdated',
        ];
    }
}
