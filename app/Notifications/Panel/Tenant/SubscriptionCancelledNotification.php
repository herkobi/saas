<?php

/**
 * Subscription Cancelled Notification
 *
 * This notification is sent to tenant owners when their subscription
 * is cancelled by an admin user.
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
 * Notification for subscription cancellation.
 *
 * Sends email and database notifications to tenant owners
 * when their subscription is cancelled.
 */
class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The cancelled subscription
     * @param bool $immediate Whether the cancellation is immediate
     */
    public function __construct(
        private readonly Subscription $subscription,
        private readonly bool $immediate
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

        $message = (new MailMessage)
            ->subject('Aboneliğiniz İptal Edildi')
            ->greeting('Merhaba ' . $notifiable->name . ',')
            ->line('Aboneliğiniz yönetici tarafından iptal edilmiştir.')
            ->line('Plan: ' . $planName);

        if ($this->immediate) {
            $message->line('Aboneliğiniz hemen sonlandırılmıştır.');
        } else {
            $endsAt = $this->subscription->ends_at?->format('d.m.Y H:i');
            $message->line('Aboneliğiniz ' . $endsAt . ' tarihine kadar aktif kalacaktır.');
        }

        return $message
            ->action('Hesabımı Görüntüle', url('/app/subscription'))
            ->line('Sorularınız için destek ekibimizle iletişime geçebilirsiniz.');
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
            'type' => 'subscription_cancelled',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->price?->plan?->name,
            'immediate' => $this->immediate,
            'ends_at' => $this->subscription->ends_at?->toISOString(),
        ];
    }
}
