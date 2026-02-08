<?php

/**
 * Send Tenant Subscription Expiry Listener
 *
 * This listener sends notifications when a tenant's subscription expires.
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

use App\Events\TenantSubscriptionExpired;
use App\Notifications\App\Account\SubscriptionExpiredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending tenant subscription expiry notifications.
 */
class SendTenantSubscriptionExpiry implements ShouldQueue
{
    /**
     * Handle the subscription expired event.
     *
     * @param TenantSubscriptionExpired $event The event instance
     * @return void
     */
    public function handle(TenantSubscriptionExpired $event): void
    {
        $owner = $event->subscription->tenant->owner();

        if ($owner) {
            $owner->notify(new SubscriptionExpiredNotification($event->subscription));
        }
    }
}
