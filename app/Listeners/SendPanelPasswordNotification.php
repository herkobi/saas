<?php

/**
 * Send Panel Password Notification Listener
 *
 * This listener handles sending notifications when a panel user's password
 * is changed. It dispatches both database and mail notifications for
 * security tracking purposes.
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

use App\Events\PanelPasswordChanged;
use App\Notifications\Panel\Profile\PasswordUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending panel password change notifications.
 *
 * Sends both database and mail notifications when a panel user's
 * password is successfully changed.
 */
class SendPanelPasswordNotification implements ShouldQueue
{
    /**
     * Handle the panel password changed event.
     *
     * @param PanelPasswordChanged $event The password changed event
     * @return void
     */
    public function handle(PanelPasswordChanged $event): void
    {
        $event->user->notify(
            new PasswordUpdatedNotification(
                $event->ipAddress,
                $event->userAgent
            )
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
            PanelPasswordChanged::class => 'handle',
        ];
    }
}
