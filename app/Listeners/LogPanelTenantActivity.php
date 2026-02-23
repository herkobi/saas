<?php

/**
 * Log Admin Tenant Activity Listener
 *
 * This listener handles the logging of admin tenant management activities.
 * It records tenant updates performed by admin users for auditing purposes.
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
use App\Events\PanelTenantFeatureOverrideUpdated;
use App\Events\PanelTenantUpdated;

/**
 * Listener for logging admin tenant activities.
 *
 * Records tenant update and feature override events with comprehensive
 * audit information for tracking and compliance purposes.
 */
class LogPanelTenantActivity
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
     * Handle the tenant updated by admin event.
     *
     * @param PanelTenantUpdated $event The tenant updated event
     * @return void
     */
    public function handleTenantUpdated(PanelTenantUpdated $event): void
    {
        $this->activityService->log(
            user: $event->admin,
            type: 'panel.tenant.updated',
            description: 'Admin kullanıcı tenant bilgilerini güncelledi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'changes' => $event->changes,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the tenant feature override updated event.
     *
     * @param PanelTenantFeatureOverrideUpdated $event The feature override updated event
     * @return void
     */
    public function handleFeatureOverrideUpdated(PanelTenantFeatureOverrideUpdated $event): void
    {
        $this->activityService->log(
            user: $event->admin,
            type: 'panel.tenant.feature_override_updated',
            description: 'Admin kullanıcı tenant özellik değerlerini güncelledi',
            log: [
                'admin_id' => $event->admin->id,
                'admin_name' => $event->admin->name,
                'admin_email' => $event->admin->email,
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'action' => $event->action,
                'old_overrides' => $event->oldOverrides,
                'new_overrides' => $event->newOverrides,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
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
            PanelTenantUpdated::class => 'handleTenantUpdated',
            PanelTenantFeatureOverrideUpdated::class => 'handleFeatureOverrideUpdated',
        ];
    }
}
