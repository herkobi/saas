<?php

/**
 * Subscription Plan Changed Mail
 *
 * This mailable is sent to tenant owners when their subscription
 * plan is changed by an admin user from the panel.
 *
 * @package    App\Mail\Panel\Tenant\Subscription
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Mail\Panel\Tenant\Subscription;

use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for subscription plan change notifications.
 *
 * Sends an email to the tenant owner informing them about
 * the plan change with old and new plan details.
 */
class SubscriptionPlanChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The tenant owner receiving the notification
     * @param Subscription $subscription The updated subscription
     * @param string $oldPlanPriceId The previous plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change is applied immediately
     */
    public function __construct(
        public readonly User $user,
        public readonly Subscription $subscription,
        public readonly string $oldPlanPriceId,
        public readonly string $newPlanPriceId,
        public readonly bool $immediate
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                settings('mail_from_address'),
                settings('mail_from_name')
            ),
            subject: 'Abonelik Planınız Değiştirildi',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        $oldPlanPrice = PlanPrice::with('plan')->find($this->oldPlanPriceId);
        $newPlanPrice = PlanPrice::with('plan')->find($this->newPlanPriceId);

        return new Content(
            markdown: 'mail.panel.tenant.subscription.plan-changed',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'oldPlanName' => $oldPlanPrice?->plan?->name ?? 'Bilinmeyen Plan',
                'newPlanName' => $newPlanPrice?->plan?->name ?? 'Bilinmeyen Plan',
                'immediate' => $this->immediate,
                'endsAt' => $this->subscription->ends_at?->format('d.m.Y H:i'),
                'accountUrl' => url('/app/subscription'),
            ],
        );
    }
}
