<?php

/**
 * Plan Upgraded Notification
 *
 * Notification sent when a subscription plan is upgraded.
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

use App\Mail\App\Account\PlanUpgradedMail;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for plan upgrade.
 *
 * Sends database and email notifications to the tenant owner.
 */
class PlanUpgradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The subscription
     * @param string $oldPlanPriceId The old plan price ID
     * @param string $newPlanPriceId The new plan price ID
     */
    public function __construct(
        private readonly Subscription $subscription,
        private readonly string $oldPlanPriceId,
        private readonly string $newPlanPriceId
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
        $oldPlanPrice = PlanPrice::with('plan')->find($this->oldPlanPriceId);
        $newPlanPrice = PlanPrice::with('plan')->find($this->newPlanPriceId);

        return [
            'tenant_id' => $this->subscription->tenant_id,
            'type' => 'subscription.upgraded',
            'title' => 'Plan Yükseltildi',
            'message' => sprintf(
                'Planınız %s\'den %s\'e yükseltildi.',
                $oldPlanPrice?->plan?->name ?? 'Eski Plan',
                $newPlanPrice?->plan?->name ?? 'Yeni Plan'
            ),
            'subscription_id' => $this->subscription->id,
            'old_plan_name' => $oldPlanPrice?->plan?->name,
            'new_plan_name' => $newPlanPrice?->plan?->name,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return PlanUpgradedMail
     */
    public function toMail(mixed $notifiable): PlanUpgradedMail
    {
        return new PlanUpgradedMail(
            $notifiable,
            $this->subscription,
            $this->oldPlanPriceId,
            $this->newPlanPriceId
        );
    }
}
