<?php

/**
 * Log Panel Profile Activity Listener
 *
 * This listener handles the logging of panel profile update activities.
 * It listens for PanelProfileUpdated events to record profile changes
 * for auditing purposes.
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
use App\Events\PanelProfileUpdated;

/**
 * Listener for logging panel profile update activities.
 *
 * Records profile update events with comprehensive audit information
 * for tracking and compliance purposes.
 */
class LogPanelProfileActivity
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
     * Handle the panel profile updated event.
     *
     * @param PanelProfileUpdated $event The profile updated event
     * @return void
     */
    public function handleUpdated(PanelProfileUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.profile_updated',
            description: 'Panel kullanıcısı profilini güncelledi',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'changes' => $event->changes,
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
            PanelProfileUpdated::class => 'handleUpdated',
        ];
    }
}
