<?php

/**
 * Trial Ending Notification
 *
 * This notification is sent when a subscription's trial period is about to end.
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

use App\Mail\App\Account\TrialEndingMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for trial ending warning.
 *
 * Sends both database and mail notifications when a subscription's
 * trial period is about to end.
 */
class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The subscription with ending trial
     * @param int $daysRemaining Days until trial ends
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
     * @return TrialEndingMail
     */
    public function toMail(mixed $notifiable): TrialEndingMail
    {
        return new TrialEndingMail(
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
            'title' => 'Deneme Süresi Bitiyor',
            'message' => "Deneme sürenizin dolmasına {$this->daysRemaining} gün kaldı.",
            'type' => 'trial_ending',
            'days_remaining' => $this->daysRemaining,
            'plan_name' => $this->subscription->price->plan->name,
            'trial_ends_at' => $this->subscription->trial_ends_at?->toDateTimeString(),
        ];
    }
}
