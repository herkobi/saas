<?php

/**
 * Plan Downgraded Notification
 *
 * Notification sent when a subscription plan is downgraded or scheduled.
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

use App\Mail\App\Account\PlanDowngradedMail;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for plan downgrade.
 *
 * Sends database and email notifications to the tenant owner.
 */
class PlanDowngradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Subscription $subscription The subscription
     * @param string $oldPlanPriceId The old plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change was applied immediately
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

        $message = $this->immediate
            ? sprintf(
                'Planınız %s\'den %s\'e düşürüldü.',
                $oldPlanPrice?->plan?->name ?? 'Eski Plan',
                $newPlanPrice?->plan?->name ?? 'Yeni Plan'
            )
            : sprintf(
                'Planınız dönem sonunda %s\'e düşürülecek.',
                $newPlanPrice?->plan?->name ?? 'Yeni Plan'
            );

        return [
            'tenant_id' => $this->subscription->tenant_id,
            'type' => 'subscription.downgraded',
            'title' => $this->immediate ? 'Plan Düşürüldü' : 'Plan Değişikliği Planlandı',
            'message' => $message,
            'subscription_id' => $this->subscription->id,
            'old_plan_name' => $oldPlanPrice?->plan?->name,
            'new_plan_name' => $newPlanPrice?->plan?->name,
            'immediate' => $this->immediate,
            'effective_at' => !$this->immediate ? $this->subscription->ends_at?->toISOString() : null,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return PlanDowngradedMail
     */
    public function toMail(mixed $notifiable): PlanDowngradedMail
    {
        return new PlanDowngradedMail(
            $notifiable,
            $this->subscription,
            $this->oldPlanPriceId,
            $this->newPlanPriceId,
            $this->immediate
        );
    }
}
