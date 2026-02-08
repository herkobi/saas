<?php

/**
 * Welcome Notification
 *
 * This notification is sent when a new tenant account is successfully registered.
 * It sends both database and mail notifications with tenant details.
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

use App\Mail\App\Account\WelcomeMail;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for tenant registration welcome.
 *
 * Sends both database and mail notifications when a new tenant
 * account is registered.
 */
class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Tenant $tenant The newly created tenant
     */
    public function __construct(
        private readonly Tenant $tenant
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
     * @return WelcomeMail
     */
    public function toMail(mixed $notifiable): WelcomeMail
    {
        return new WelcomeMail($notifiable, $this->tenant);
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
            'tenant_id' => $this->tenant->id,
            'title' => 'Hoş Geldiniz!',
            'message' => 'Hesabınız başarıyla oluşturuldu. Sisteme hoş geldiniz.',
            'type' => 'welcome',
            'tenant_code' => $this->tenant->code,
            'registered_at' => now()->toDateTimeString(),
        ];
    }
}
