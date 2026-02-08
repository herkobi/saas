<?php

/**
 * Subscription Expired Notification
 *
 * This notification is sent when a tenant's subscription has expired.
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

use App\Mail\App\Account\SubscriptionExpiredMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for subscription expiration.
 *
 * Sends both database and mail notifications when a subscription expires.
 */
class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The expired subscription
     */
    public function __construct(
        private readonly Subscription $subscription
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
     * @return SubscriptionExpiredMail
     */
    public function toMail(mixed $notifiable): SubscriptionExpiredMail
    {
        return new SubscriptionExpiredMail($notifiable, $this->subscription);
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
            'tenant_id' => $this->subscription->tenant_id,
            'subscription_id' => $this->subscription->id,
            'title' => 'Abonelik Süresi Doldu',
            'message' => 'Aboneliğinizin süresi doldu. Hizmetlere erişiminiz kısıtlandı.',
            'type' => 'subscription_expired',
            'plan_name' => $this->subscription->price->plan->name,
            'expired_at' => $this->subscription->ends_at?->toDateTimeString(),
        ];
    }
}
