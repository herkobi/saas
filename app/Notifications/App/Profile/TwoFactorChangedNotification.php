<?php

/**
 * Tenant Two Factor Changed Notification
 *
 * This notification is sent when a tenant user enables or disables
 * two-factor authentication. It sends database notification to inform
 * the user about the security change.
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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for tenant two-factor authentication changes.
 *
 * Sends database notification when a tenant user enables or disables
 * two-factor authentication.
 */
class TwoFactorChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $tenantId The tenant ID
     * @param bool $enabled Whether two-factor was enabled or disabled
     * @param string $ipAddress IP address from which the change was made
     * @param string $userAgent User agent from which the change was made
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly bool $enabled,
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
        return ['database'];
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
            'title' => $this->enabled ? 'İki Faktörlü Doğrulama Etkinleştirildi' : 'İki Faktörlü Doğrulama Devre Dışı Bırakıldı',
            'message' => $this->enabled
                ? 'Hesabınız için iki faktörlü doğrulama başarıyla etkinleştirildi.'
                : 'Hesabınız için iki faktörlü doğrulama devre dışı bırakıldı.',
            'type' => 'security',
            'enabled' => $this->enabled,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'changed_at' => now()->toDateTimeString(),
        ];
    }
}
