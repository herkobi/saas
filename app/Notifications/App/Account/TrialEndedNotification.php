<?php

/**
 * Trial Ended Notification
 *
 * This notification is sent when a tenant's trial period has ended.
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

use App\Mail\App\Account\TrialEndedMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for trial period ending.
 *
 * Sends both database and mail notifications when a trial ends.
 */
class TrialEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The subscription with ended trial
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
     * @return TrialEndedMail
     */
    public function toMail(mixed $notifiable): TrialEndedMail
    {
        return new TrialEndedMail($notifiable, $this->subscription);
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
            'title' => 'Deneme Süresi Sona Erdi',
            'message' => 'Deneme süreniz sona erdi. Hizmetlere erişiminiz kısıtlandı.',
            'type' => 'trial_ended',
            'plan_name' => $this->subscription->price->plan->name,
            'trial_ends_at' => $this->subscription->trial_ends_at?->toDateTimeString(),
        ];
    }
}
