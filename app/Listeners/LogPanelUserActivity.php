<?php

/**
 * Log Panel User Activity Listener
 *
 * This listener handles the logging of panel user management activities.
 * It records user status updates performed by admin users for auditing purposes.
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
use App\Events\PanelUserStatusUpdated;

/**
 * Listener for logging panel user activities.
 *
 * Records user management events with comprehensive audit information
 * for tracking and compliance purposes.
 */
class LogPanelUserActivity
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
     * Handle the user status updated by admin event.
     *
     * @param PanelUserStatusUpdated $event The user status updated event
     * @return void
     */
    public function handleUserStatusUpdated(PanelUserStatusUpdated $event): void
    {
        $user = $event->user;

        $this->activityService->log(
            user: $event->admin,
            type: 'panel.user.status_updated',
            description: 'Admin kullanıcı durumunu manuel olarak değiştirdi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
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
            PanelUserStatusUpdated::class => 'handleUserStatusUpdated',
        ];
    }
}
