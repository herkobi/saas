<?php

/**
 * Send Panel Profile Notification Listener
 *
 * This listener handles sending notifications when a panel user's profile
 * is updated. It dispatches database notification for tracking purposes.
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

use App\Events\PanelProfileUpdated;
use App\Notifications\Panel\Profile\ProfileUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending panel profile update notifications.
 *
 * Sends database notification when a panel user's profile is updated.
 */
class SendPanelProfileNotification implements ShouldQueue
{
    /**
     * Handle the panel profile updated event.
     *
     * @param PanelProfileUpdated $event The profile updated event
     * @return void
     */
    public function handle(PanelProfileUpdated $event): void
    {
        $event->user->notify(
            new ProfileUpdatedNotification($event->changes)
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
            PanelProfileUpdated::class => 'handle',
        ];
    }
}
