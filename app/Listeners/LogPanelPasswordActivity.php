<?php

/**
 * Log Panel Password Activity Listener
 *
 * This listener handles the logging of panel password change activities.
 * It listens for PanelPasswordChanged events to record password changes
 * for security auditing purposes.
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
use App\Events\PanelPasswordChanged;

/**
 * Listener for logging panel password change activities.
 *
 * Records password change events with comprehensive audit information
 * for security tracking and compliance purposes.
 */
class LogPanelPasswordActivity
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
     * Handle the panel password changed event.
     *
     * @param PanelPasswordChanged $event The password changed event
     * @return void
     */
    public function handle(PanelPasswordChanged $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.password_changed',
            description: 'Panel kullanıcısı şifresini değiştirdi',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'changed_at' => now()->toDateTimeString(),
            ]
        );
    }
}
