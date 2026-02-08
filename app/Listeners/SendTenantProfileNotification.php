<?php

/**
 * Send Tenant Profile Notification Listener
 *
 * This listener handles sending notifications when a tenant user's profile
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

use App\Events\TenantProfileUpdated;
use App\Notifications\App\Profile\ProfileUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending tenant profile update notifications.
 *
 * Sends database notification when a tenant user's profile is updated.
 */
class SendTenantProfileNotification implements ShouldQueue
{
    /**
     * Handle the tenant profile updated event.
     *
     * @param TenantProfileUpdated $event The profile updated event
     * @return void
     */
    public function handle(TenantProfileUpdated $event): void
    {
        $event->user->notify(
            new ProfileUpdatedNotification($event->tenant->id, $event->changes)
        );
    }
}
