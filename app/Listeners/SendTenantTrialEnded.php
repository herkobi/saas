<?php

/**
 * Send Tenant Trial Ended Listener
 *
 * This listener sends notifications when a tenant's trial period ends.
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

use App\Events\TenantTrialEnded;
use App\Notifications\App\Account\TrialEndedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending trial ended notifications.
 */
class SendTenantTrialEnded implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param TenantTrialEnded $event The event instance
     * @return void
     */
    public function handle(TenantTrialEnded $event): void
    {
        $owner = $event->subscription->tenant->owner();

        if ($owner) {
            $owner->notify(new TrialEndedNotification($event->subscription));
        }
    }
}
