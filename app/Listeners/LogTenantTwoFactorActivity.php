<?php

/**
 * Log Tenant Two Factor Activity Listener
 *
 * This listener handles the logging of tenant two-factor authentication activities.
 * It listens for TenantTwoFactorEnabled and TenantTwoFactorDisabled events to record
 * two-factor changes for security auditing purposes.
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

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\TenantTwoFactorDisabled;
use App\Events\TenantTwoFactorEnabled;

/**
 * Listener for logging tenant two-factor authentication activities.
 *
 * Records two-factor enable/disable events with comprehensive audit information
 * for security tracking and compliance purposes.
 */
class LogTenantTwoFactorActivity
{
    /**
     * Create the event listener.
     *
     * @param ActivityServiceInterface $activityService Service for logging activities
     */
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle the tenant two-factor enabled event.
     *
     * @param TenantTwoFactorEnabled $event The two-factor enabled event
     * @return void
     */
    public function handleEnabled(TenantTwoFactorEnabled $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.two_factor_enabled',
            description: 'Tenant kullanıcısı iki faktörlü doğrulamayı etkinleştirdi',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'enabled_at' => now()->toDateTimeString(),
            ],
            tenantId: $event->tenant->id
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
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.two_factor_disabled',
            description: 'Tenant kullanıcısı iki faktörlü doğrulamayı devre dışı bıraktı',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'disabled_at' => now()->toDateTimeString(),
            ],
            tenantId: $event->tenant->id
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
