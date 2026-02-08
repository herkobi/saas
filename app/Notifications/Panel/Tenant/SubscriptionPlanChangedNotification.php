<?php

/**
 * Subscription Plan Changed Notification
 *
 * This notification is sent to tenant owners when their subscription
 * plan is changed by an admin user.
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

use App\Models\PlanPrice;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for subscription plan change.
 *
 * Sends email and database notifications to tenant owners
 * when their subscription plan is changed.
 */
class SubscriptionPlanChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The updated subscription
     * @param string $oldPlanPriceId The previous plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change is applied immediately
     */
    public function __construct(
        private readonly Subscription $subscription,
        private readonly string $oldPlanPriceId,
        private readonly string $newPlanPriceId,
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
        $oldPlanPrice = PlanPrice::with('plan')->find($this->oldPlanPriceId);
        $newPlanPrice = PlanPrice::with('plan')->find($this->newPlanPriceId);

        $oldPlanName = $oldPlanPrice?->plan?->name ?? 'Bilinmeyen Plan';
        $newPlanName = $newPlanPrice?->plan?->name ?? 'Bilinmeyen Plan';

        $message = (new MailMessage)
            ->subject('Abonelik Planınız Değiştirildi')
            ->greeting('Merhaba ' . $notifiable->name . ',')
            ->line('Abonelik planınız yönetici tarafından değiştirilmiştir.')
            ->line('Eski Plan: ' . $oldPlanName)
            ->line('Yeni Plan: ' . $newPlanName);

        if ($this->immediate) {
            $message->line('Değişiklik hemen uygulanmıştır.');
        } else {
            $endsAt = $this->subscription->ends_at?->format('d.m.Y H:i');
            $message->line('Değişiklik mevcut dönem sonunda (' . $endsAt . ') uygulanacaktır.');
        }

        return $message
            ->action('Planımı Görüntüle', url('/app/subscription'))
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
            'type' => 'subscription_plan_changed',
            'subscription_id' => $this->subscription->id,
            'old_plan_price_id' => $this->oldPlanPriceId,
            'new_plan_price_id' => $this->newPlanPriceId,
            'immediate' => $this->immediate,
            'ends_at' => $this->subscription->ends_at?->toISOString(),
        ];
    }
}
