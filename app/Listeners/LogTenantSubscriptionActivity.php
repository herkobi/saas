<?php

/**
 * Log Tenant Subscription Activity Listener
 *
 * This listener logs tenant subscription lifecycle events to the activity log
 * for audit and tracking purposes.
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

use App\Services\Shared\ActivityService;
use App\Events\TenantMeteredUsageReset;
use App\Events\TenantSubscriptionExpired;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for logging tenant subscription activities.
 *
 * Records all subscription state changes to the activity log
 * for audit trail purposes.
 */
class LogTenantSubscriptionActivity implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @param ActivityService $activityService The activity logging service
     */
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Handle the subscription expired event.
     *
     * @param TenantSubscriptionExpired $event The event instance
     * @return void
     */
    public function handleExpired(TenantSubscriptionExpired $event): void
    {
        $owner = $event->subscription->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.expired',
                description: 'Abonelik süresi doldu',
                log: [
                    'subscription_id' => $event->subscription->id,
                    'plan_name' => $event->subscription->price->plan->name,
                    'ends_at' => $event->subscription->ends_at?->toDateTimeString(),
                ],
                tenantId: $event->subscription->tenant_id
            );
        }
    }

    /**
     * Handle the metered usage reset event.
     *
     * @param TenantMeteredUsageReset $event The event instance
     * @return void
     */
    public function handleUsageReset(TenantMeteredUsageReset $event): void
    {
        $this->activityService->logAnonymousActivity(
            type: 'feature.usage_reset',
            description: 'Sayaçlı kullanım sıfırlandı',
            log: [
                'tenant_id' => $event->usage->tenant_id,
                'feature_id' => $event->usage->feature_id,
                'feature_code' => $event->usage->feature->slug,
                'previous_used' => $event->previousUsed,
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param object $events The events dispatcher
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            TenantSubscriptionExpired::class => 'handleExpired',
            TenantMeteredUsageReset::class => 'handleUsageReset',
        ];
    }
}
