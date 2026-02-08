<?php

/**
 * Log Tenant Registration Activity Listener
 *
 * This listener handles the logging of tenant registration activities.
 * It listens for TenantRegistered events to record new tenant registrations
 * for auditing purposes.
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
use App\Events\TenantRegistered;

/**
 * Listener for logging tenant registration activities.
 *
 * Records tenant registration events with comprehensive audit information
 * for tracking and compliance purposes.
 */
class LogTenantRegistrationActivity
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
     * Handle the tenant registered event.
     *
     * @param TenantRegistered $event The tenant registered event
     * @return void
     */
    public function handleRegistration(TenantRegistered $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.registered',
            description: 'Yeni bir hesap (tenant) oluşturuldu',
            log: [
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'registered_at' => now()->toDateTimeString(),
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
            TenantRegistered::class => 'handleRegistration',
        ];
    }
}
