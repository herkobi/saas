<?php

/**
 * Send Panel Two Factor Notification Listener
 *
 * This listener handles sending notifications when a panel user's two-factor
 * authentication status is changed. It dispatches database notification for
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

use App\Events\PanelTwoFactorDisabled;
use App\Events\PanelTwoFactorEnabled;
use App\Notifications\Panel\Profile\TwoFactorChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending panel two-factor authentication notifications.
 *
 * Sends database notification when a panel user enables or disables
 * two-factor authentication.
 */
class SendPanelTwoFactorNotification implements ShouldQueue
{
    /**
     * Handle the panel two-factor enabled event.
     *
     * @param PanelTwoFactorEnabled $event The two-factor enabled event
     * @return void
     */
    public function handleEnabled(PanelTwoFactorEnabled $event): void
    {
        $event->user->notify(
            new TwoFactorChangedNotification(
                true,
                $event->ipAddress,
                $event->userAgent
            )
        );
    }

    /**
     * Handle the panel two-factor disabled event.
     *
     * @param PanelTwoFactorDisabled $event The two-factor disabled event
     * @return void
     */
    public function handleDisabled(PanelTwoFactorDisabled $event): void
    {
        $event->user->notify(
            new TwoFactorChangedNotification(
                false,
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
            PanelTwoFactorEnabled::class => 'handleEnabled',
            PanelTwoFactorDisabled::class => 'handleDisabled',
        ];
    }
}
