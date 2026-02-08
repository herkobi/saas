<?php

/**
 * Log Tenant Profile Activity Listener
 *
 * This listener handles the logging of tenant profile activities.
 * It listens for TenantProfileUpdated and TenantProfileDeleted events
 * to record profile changes for auditing purposes.
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
use App\Events\TenantProfileUpdated;

/**
 * Listener for logging tenant profile activities.
 *
 * Records profile update and deletion events with comprehensive audit information
 * for tracking and compliance purposes.
 */
class LogTenantProfileActivity
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
     * Handle the tenant profile updated event.
     *
     * @param TenantProfileUpdated $event The profile updated event
     * @return void
     */
    public function handleUpdated(TenantProfileUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.profile_updated',
            description: 'Tenant kullanıcısı profilini güncelledi',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'changes' => $event->changes,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ],
            tenantId: $event->tenant->id
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
            TenantProfileUpdated::class => 'handleUpdated',
        ];
    }
}
