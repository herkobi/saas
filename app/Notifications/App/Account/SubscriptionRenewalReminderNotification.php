<?php

/**
 * Subscription Renewal Reminder Notification
 *
 * This notification is sent to remind tenant owners about
 * upcoming subscription expiration.
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

use App\Mail\App\Account\SubscriptionRenewalReminderMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for subscription renewal reminders.
 *
 * Sends both database and mail notifications when a subscription
 * is approaching expiration.
 */
class SubscriptionRenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The subscription nearing expiration
     * @param int $daysRemaining Days until expiration
     */
    public function __construct(
        private readonly Subscription $subscription,
        private readonly int $daysRemaining
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
     * @return SubscriptionRenewalReminderMail
     */
    public function toMail(mixed $notifiable): SubscriptionRenewalReminderMail
    {
        return new SubscriptionRenewalReminderMail(
            $notifiable,
            $this->subscription,
            $this->daysRemaining
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
            'tenant_id' => $this->subscription->tenant_id,
            'subscription_id' => $this->subscription->id,
            'title' => 'Abonelik Yenileme Hatırlatması',
            'message' => "Aboneliğinizin süresinin dolmasına {$this->daysRemaining} gün kaldı.",
            'type' => 'subscription_renewal_reminder',
            'days_remaining' => $this->daysRemaining,
            'plan_name' => $this->subscription->price->plan->name,
            'ends_at' => $this->subscription->ends_at?->toDateTimeString(),
        ];
    }
}
