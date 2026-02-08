<?php

/**
 * Tenant Password Updated Notification
 *
 * This notification is sent when a tenant user's password is successfully changed.
 * It sends both database and mail notifications to inform the user about
 * the password change for security purposes.
 *
 * @package    App\Notifications\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Notifications\App\Profile;

use App\Mail\App\Profile\PasswordUpdatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for tenant password updates.
 *
 * Sends both database and mail notifications when a tenant user's
 * password is changed for security tracking.
 */
class PasswordUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $tenantId The tenant ID
     * @param string $ipAddress IP address from which the password was changed
     * @param string $userAgent User agent from which the password was changed
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $ipAddress,
        private readonly string $userAgent
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return PasswordUpdatedMail
     */
    public function toMail(mixed $notifiable): PasswordUpdatedMail
    {
        return new PasswordUpdatedMail(
            $notifiable,
            $this->ipAddress,
            $this->userAgent
        );
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
            'tenant_id' => $this->tenantId,
            'title' => 'Şifreniz Değiştirildi',
            'message' => 'Hesabınızın şifresi başarıyla değiştirildi.',
            'type' => 'security',
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'changed_at' => now()->toDateTimeString(),
        ];
    }
}
