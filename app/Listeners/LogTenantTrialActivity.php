<?php

/**
 * Log Tenant Trial Activity Listener
 *
 * This listener logs tenant trial lifecycle events to the activity log
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

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\TenantTrialEnded;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for logging tenant trial activities.
 */
class LogTenantTrialActivity implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @param ActivityServiceInterface $activityService The activity logging service
     */
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle the trial ended event.
     *
     * @param TenantTrialEnded $event The event instance
     * @return void
     */
    public function handle(TenantTrialEnded $event): void
    {
        $owner = $event->subscription->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'subscription.trial_ended',
                description: 'Deneme süresi sona erdi',
                log: [
                    'subscription_id' => $event->subscription->id,
                    'plan_name' => $event->subscription->price->plan->name,
                    'trial_ends_at' => $event->subscription->trial_ends_at?->toDateTimeString(),
                ],
                tenantId: $event->subscription->tenant_id
            );
        }
    }
}
