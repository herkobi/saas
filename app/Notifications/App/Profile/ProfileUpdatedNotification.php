<?php

/**
 * Tenant Profile Updated Notification
 *
 * This notification is sent when a tenant user's profile is successfully updated.
 * It sends only database notification to inform the user about the profile changes.
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
 * Notification for tenant profile updates.
 *
 * Sends database notification when a tenant user's profile is updated.
 */
class ProfileUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $tenantId The tenant ID
     * @param array<string, mixed> $changes The changes made to the profile
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly array $changes
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
            'title' => 'Profiliniz Güncellendi',
            'message' => 'Profil bilgileriniz başarıyla güncellendi.',
            'type' => 'info',
            'changes' => $this->changes,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
