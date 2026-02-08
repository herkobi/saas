<?php

/**
 * Log User Management Activity Listener
 *
 * Handles activity logging and notifications for user management events
 * including role changes and user removals.
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
use App\Enums\TenantUserRole;
use App\Events\TenantUserRemoved;
use App\Events\TenantUserRoleChanged;
use App\Notifications\App\Account\UserRemovedFromTenantNotification;
use App\Notifications\App\Account\UserRoleChangedNotification;
use Illuminate\Events\Dispatcher;

/**
 * Listener for user management activity logging.
 */
class LogTenantUserActivity
{
    /**
     * Create a new listener instance.
     *
     * @param ActivityServiceInterface $activityService The activity service
     */
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle user role changed event.
     *
     * @param TenantUserRoleChanged $event The event
     * @return void
     */
    public function handleRoleChanged(TenantUserRoleChanged $event): void
    {
        $oldRoleLabel = $event->oldRole !== null
            ? TenantUserRole::from($event->oldRole)->label()
            : 'Yok';
        $newRoleLabel = TenantUserRole::from($event->newRole)->label();

        $this->activityService->log(
            user: $event->changedBy,
            type: 'tenant.user_role_changed',
            description: 'Kullanıcı rolü değiştirildi',
            log: [
                'tenant_id' => $event->tenant->id,
                'target_user_id' => $event->user->id,
                'target_user_email' => $event->user->email,
                'old_role' => $event->oldRole,
                'old_role_label' => $oldRoleLabel,
                'new_role' => $event->newRole,
                'new_role_label' => $newRoleLabel,
                'changed_by_name' => $event->changedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );

        $event->user->notify(new UserRoleChangedNotification(
            $event->tenant,
            $event->oldRole,
            $event->newRole
        ));
    }

    /**
     * Handle user removed event.
     *
     * @param TenantUserRemoved $event The event
     * @return void
     */
    public function handleUserRemoved(TenantUserRemoved $event): void
    {
        $roleLabel = $event->role !== null
            ? TenantUserRole::from($event->role)->label()
            : 'Bilinmiyor';

        $this->activityService->log(
            user: $event->removedBy,
            type: 'tenant.user_removed',
            description: 'Kullanıcı hesaptan çıkarıldı',
            log: [
                'tenant_id' => $event->tenant->id,
                'removed_user_id' => $event->user->id,
                'removed_user_email' => $event->user->email,
                'role' => $event->role,
                'role_label' => $roleLabel,
                'removed_by_name' => $event->removedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );

        $event->user->notify(new UserRemovedFromTenantNotification($event->tenant));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events The event dispatcher
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantUserRoleChanged::class => 'handleRoleChanged',
            TenantUserRemoved::class => 'handleUserRemoved',
        ];
    }
}
