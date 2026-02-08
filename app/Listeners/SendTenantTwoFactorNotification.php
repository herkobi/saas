<?php

/**
 * Send Tenant Two Factor Notification Listener
 *
 * This listener handles sending notifications when a tenant user's two-factor
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

use App\Events\TenantTwoFactorDisabled;
use App\Events\TenantTwoFactorEnabled;
use App\Notifications\App\Profile\TwoFactorChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending tenant two-factor authentication notifications.
 *
 * Sends database notification when a tenant user enables or disables
 * two-factor authentication.
 */
class SendTenantTwoFactorNotification implements ShouldQueue
{
    /**
     * Handle the tenant two-factor enabled event.
     *
     * @param TenantTwoFactorEnabled $event The two-factor enabled event
     * @return void
     */
    public function handleEnabled(TenantTwoFactorEnabled $event): void
    {
        $event->user->notify(
            new TwoFactorChangedNotification(
                $event->tenant->id,
                true,
                $event->ipAddress,
                $event->userAgent
            )
        );
    }

    /**
     * Handle the tenant two-factor disabled event.
     *
     * @param TenantTwoFactorDisabled $event The two-factor disabled event
     * @return void
     */
    public function handleDisabled(TenantTwoFactorDisabled $event): void
    {
        $event->user->notify(
            new TwoFactorChangedNotification(
                $event->tenant->id,
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
            TenantTwoFactorEnabled::class => 'handleEnabled',
            TenantTwoFactorDisabled::class => 'handleDisabled',
        ];
    }
}
