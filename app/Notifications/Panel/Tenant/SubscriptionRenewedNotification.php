<?php

/**
 * Subscription Renewed Notification
 *
 * This notification is sent to tenant owners when their subscription
 * is renewed by an admin user.
 *
 * @package    App\Notifications\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Notifications\Panel\Tenant;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for subscription renewal.
 *
 * Sends email and database notifications to tenant owners
 * when their subscription is renewed.
 */
class SubscriptionRenewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The renewed subscription
     */
    public function __construct(
        private readonly Subscription $subscription
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $planName = $this->subscription->price?->plan?->name ?? 'Bilinmeyen Plan';
        $endsAt = $this->subscription->ends_at?->format('d.m.Y H:i');

        return (new MailMessage)
            ->subject('Aboneliğiniz Yenilendi')
            ->greeting('Merhaba ' . $notifiable->name . ',')
            ->line('Aboneliğiniz yönetici tarafından yenilenmiştir.')
            ->line('Plan: ' . $planName)
            ->line('Yeni bitiş tarihi: ' . $endsAt)
            ->action('Planımı Görüntüle', url('/app/subscription'))
            ->line('Hizmetlerimizi kullanmaya devam ettiğiniz için teşekkür ederiz.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_renewed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->price?->plan?->name,
            'starts_at' => $this->subscription->starts_at?->toISOString(),
            'ends_at' => $this->subscription->ends_at?->toISOString(),
        ];
    }
}
