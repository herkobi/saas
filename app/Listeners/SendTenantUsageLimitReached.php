<?php

/**
 * Send Tenant Usage Limit Reached Listener
 *
 * This listener sends notifications when a tenant reaches
 * a feature usage limit.
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

use App\Events\TenantUsageLimitReached;
use App\Notifications\App\Account\UsageLimitReachedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for sending usage limit reached notifications.
 */
class SendTenantUsageLimitReached implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param TenantUsageLimitReached $event The event instance
     * @return void
     */
    public function handle(TenantUsageLimitReached $event): void
    {
        $owner = $event->tenant->owner();

        if ($owner) {
            $owner->notify(new UsageLimitReachedNotification(
                $event->tenant->id,
                $event->feature,
                $event->currentUsage,
                $event->limit
            ));
        }
    }
}
