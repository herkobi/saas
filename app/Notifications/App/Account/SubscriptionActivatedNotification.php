<?php

/**
 * Subscription Activated Notification
 *
 * Notification sent when a new subscription is activated.
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

use App\Mail\App\Account\SubscriptionActivatedMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for subscription activation.
 *
 * Sends database and email notifications to the tenant owner.
 */
class SubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The activated subscription
     */
    public function __construct(
        private readonly Subscription $subscription
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        $this->subscription->load('price.plan');

        return [
            'tenant_id' => $this->subscription->tenant_id,
            'type' => 'subscription.activated',
            'title' => 'Abonelik Aktif',
            'message' => sprintf(
                '%s planınız aktif edildi.',
                $this->subscription->price->plan->name ?? 'Abonelik'
            ),
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->price->plan->name ?? null,
            'ends_at' => $this->subscription->ends_at?->toISOString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return SubscriptionActivatedMail
     */
    public function toMail(mixed $notifiable): SubscriptionActivatedMail
    {
        return new SubscriptionActivatedMail($notifiable, $this->subscription);
    }
}
