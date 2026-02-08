<?php

/**
 * Plan Downgraded Mail
 *
 * This mailable is sent when a subscription plan is downgraded or scheduled.
 *
 * @package    App\Mail\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Mail\App\Account;

use App\Helpers\CurrencyHelper;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for plan downgrade notifications.
 *
 * Sends an email with downgrade details and effective date.
 */
class PlanDowngradedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user receiving the notification
     * @param Subscription $subscription The subscription
     * @param string $oldPlanPriceId The old plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change was applied immediately
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
        $subject = $this->immediate
            ? 'Plan Düşürüldü'
            : 'Plan Değişikliği Planlandı';

        return new Envelope(
            subject: $subject,
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
            markdown: 'mail.tenant.account.plan-downgraded',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'oldPlanName' => $oldPlanPrice?->plan?->name ?? 'Eski Plan',
                'newPlanName' => $newPlanPrice?->plan?->name ?? 'Yeni Plan',
                'newPrice' => number_format((float) ($newPlanPrice?->price ?? 0), 2),
                'currency' => $newPlanPrice?->currency ?? CurrencyHelper::defaultCode(),
                'immediate' => $this->immediate,
                'effectiveAt' => !$this->immediate ? $this->subscription->ends_at?->format('d.m.Y') : null,
                'dashboardUrl' => url('/app/dashboard'),
            ],
        );
    }
}
