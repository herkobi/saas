<?php

/**
 * Log Tenant Password Activity Listener
 *
 * This listener handles the logging of tenant password change activities.
 * It listens for TenantPasswordChanged events to record password changes
 * for security auditing purposes.
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

use App\Services\Shared\ActivityService;
use App\Events\TenantPasswordChanged;

/**
 * Listener for logging tenant password change activities.
 *
 * Records password change events with comprehensive audit information
 * for security tracking and compliance purposes.
 */
class LogTenantPasswordActivity
{
    /**
     * Create the event listener.
     *
     * @param ActivityService $activityService Service for logging activities
     */
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Handle the tenant password changed event.
     *
     * @param TenantPasswordChanged $event The password changed event
     * @return void
     */
    public function handle(TenantPasswordChanged $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.password_changed',
            description: 'Tenant kullanıcısı şifresini değiştirdi',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'changed_at' => now()->toDateTimeString(),
            ],
            tenantId: $event->tenant->id
        );
    }
}
