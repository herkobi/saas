<?php

/**
 * User Removed From Tenant Notification
 *
 * This notification is sent to users when they are
 * removed from a tenant.
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

use App\Mail\App\Account\UserRemovedFromTenantMail;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for user removal from tenant.
 */
class UserRemovedFromTenantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Tenant $tenant The tenant the user was removed from
     */
    public function __construct(
        private readonly Tenant $tenant
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
     * @return UserRemovedFromTenantMail
     */
    public function toMail(mixed $notifiable): UserRemovedFromTenantMail
    {
        return (new UserRemovedFromTenantMail($notifiable, $this->tenant))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'tenant.user_removed',
            'title' => 'Hesaptan Çıkarıldınız',
            'message' => "'{$this->tenant->code}' hesabından çıkarıldınız.",
            'tenant_id' => $this->tenant->id,
            'tenant_code' => $this->tenant->code,
            'tenant_name' => $this->tenant->data['name'] ?? $this->tenant->code,
        ];
    }
}
