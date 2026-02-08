<?php

/**
 * User Role Changed Notification
 *
 * This notification is sent to users when their role
 * is changed within a tenant.
 *
 * @package    App\Notifications\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Notifications\App\Account;

use App\Enums\TenantUserRole;
use App\Mail\App\Account\UserRoleChangedMail;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for user role changes.
 */
class UserRoleChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Tenant $tenant The tenant context
     * @param int|null $oldRole The previous role value
     * @param int $newRole The new role value
     */
    public function __construct(
        private readonly Tenant $tenant,
        private readonly ?int $oldRole,
        private readonly int $newRole
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return UserRoleChangedMail
     */
    public function toMail(mixed $notifiable): UserRoleChangedMail
    {
        return (new UserRoleChangedMail(
            $notifiable,
            $this->tenant,
            $this->oldRole,
            $this->newRole
        ))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        $oldRoleLabel = $this->oldRole !== null
            ? TenantUserRole::from($this->oldRole)->label()
            : 'Yok';
        $newRoleLabel = TenantUserRole::from($this->newRole)->label();

        return [
            'type' => 'tenant.user_role_changed',
            'title' => 'Rolünüz Değiştirildi',
            'message' => "'{$this->tenant->code}' hesabındaki rolünüz {$oldRoleLabel} yerine {$newRoleLabel} olarak değiştirildi.",
            'tenant_id' => $this->tenant->id,
            'tenant_code' => $this->tenant->code,
            'tenant_name' => $this->tenant->data['name'] ?? $this->tenant->code,
            'old_role' => $this->oldRole,
            'old_role_label' => $oldRoleLabel,
            'new_role' => $this->newRole,
            'new_role_label' => $newRoleLabel,
        ];
    }
}
