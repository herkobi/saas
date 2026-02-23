<?php

/**
 * Log Tenant Usage Limit Activity Listener
 *
 * This listener logs when a tenant reaches a feature usage limit
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
use App\Events\TenantUsageLimitReached;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for logging tenant usage limit activities.
 */
class LogTenantUsageLimitActivity implements ShouldQueue
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
     * Handle the usage limit reached event.
     *
     * @param TenantUsageLimitReached $event The event instance
     * @return void
     */
    public function handle(TenantUsageLimitReached $event): void
    {
        $owner = $event->tenant->owner();

        if ($owner) {
            $this->activityService->log(
                user: $owner,
                type: 'feature.limit_reached',
                description: "{$event->feature->name} kullanım limiti doldu",
                log: [
                    'feature_id' => $event->feature->id,
                    'feature_code' => $event->feature->code,
                    'feature_name' => $event->feature->name,
                    'current_usage' => $event->currentUsage,
                    'limit' => $event->limit,
                ],
                tenantId: $event->tenant->id
            );
        }
    }
}
