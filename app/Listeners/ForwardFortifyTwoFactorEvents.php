<?php

/**
 * Forward Fortify Two Factor Events Listener
 *
 * This listener forwards Laravel Fortify's two-factor authentication events
 * to application-specific events based on user type for proper handling
 * and auditing purposes.
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

use App\Enums\UserType;
use App\Events\PanelTwoFactorDisabled;
use App\Events\PanelTwoFactorEnabled;
use App\Events\TenantTwoFactorDisabled;
use App\Events\TenantTwoFactorEnabled;
use Illuminate\Support\Facades\Request;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;

/**
 * Listener for forwarding Fortify two-factor authentication events.
 *
 * Routes Fortify's generic two-factor events to user-type specific
 * application events for proper segregation and handling.
 */
class ForwardFortifyTwoFactorEvents
{
    /**
     * Handle the two-factor authentication enabled event.
     *
     * @param TwoFactorAuthenticationEnabled $event The Fortify event
     * @return void
     */
    public function handleEnabled(TwoFactorAuthenticationEnabled $event): void
    {
        $ipAddress = Request::ip() ?? '127.0.0.1';
        $userAgent = Request::userAgent() ?? 'unknown';

        if ($event->user->user_type === UserType::TENANT) {
            event(new TenantTwoFactorEnabled(
                $event->user,
                $event->user->currentTenant(),
                $ipAddress,
                $userAgent
            ));
        } elseif ($event->user->user_type === UserType::ADMIN) {
            event(new PanelTwoFactorEnabled(
                $event->user,
                $ipAddress,
                $userAgent
            ));
        }
    }

    /**
     * Handle the two-factor authentication disabled event.
     *
     * @param TwoFactorAuthenticationDisabled $event The Fortify event
     * @return void
     */
    public function handleDisabled(TwoFactorAuthenticationDisabled $event): void
    {
        $ipAddress = Request::ip() ?? '127.0.0.1';
        $userAgent = Request::userAgent() ?? 'unknown';

        if ($event->user->user_type === UserType::TENANT) {
            event(new TenantTwoFactorDisabled(
                $event->user,
                $event->user->currentTenant(),
                $ipAddress,
                $userAgent
            ));
        } elseif ($event->user->user_type === UserType::ADMIN) {
            event(new PanelTwoFactorDisabled(
                $event->user,
                $ipAddress,
                $userAgent
            ));
        }
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
            TwoFactorAuthenticationEnabled::class => 'handleEnabled',
            TwoFactorAuthenticationDisabled::class => 'handleDisabled',
        ];
    }
}
