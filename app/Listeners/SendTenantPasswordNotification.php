<?php

/**
 * Send Tenant Password Notification Listener
 *
 * This listener handles sending notifications when a tenant user's password
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

use App\Events\TenantPasswordChanged;
use App\Notifications\App\Profile\PasswordUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending tenant password change notifications.
 *
 * Sends both database and mail notifications when a tenant user's
 * password is successfully changed.
 */
class SendTenantPasswordNotification implements ShouldQueue
{
    /**
     * Handle the tenant password changed event.
     *
     * @param TenantPasswordChanged $event The password changed event
     * @return void
     */
    public function handle(TenantPasswordChanged $event): void
    {
        $event->user->notify(
            new PasswordUpdatedNotification(
                $event->tenant->id,
                $event->ipAddress,
                $event->userAgent
            )
        );
    }
}
