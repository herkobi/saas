<?php

/**
 * Send Tenant Welcome Email Listener
 *
 * This listener handles sending welcome emails to newly registered tenants.
 * It listens for TenantRegistered events and dispatches welcome notifications
 * via queue for better performance.
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

use App\Events\TenantRegistered;
use App\Notifications\App\Account\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Listener for sending tenant welcome emails.
 *
 * Sends welcome notification to newly registered tenant users
 * with their account and tenant information.
 */
class SendTenantWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the tenant registered event.
     *
     * @param TenantRegistered $event The tenant registered event
     * @return void
     */
    public function handleWelcome(TenantRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification($event->tenant));
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
            TenantRegistered::class => 'handleWelcome',
        ];
    }
}
