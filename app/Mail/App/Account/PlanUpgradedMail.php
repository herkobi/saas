<?php

/**
 * Plan Upgraded Mail
 *
 * This mailable is sent when a subscription plan is upgraded.
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
 * Mailable for plan upgrade notifications.
 *
 * Sends an email with upgrade details and new plan information.
 */
class PlanUpgradedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user receiving the notification
     * @param Subscription $subscription The subscription
     * @param string $oldPlanPriceId The old plan price ID
     * @param string $newPlanPriceId The new plan price ID
     */
    public function __construct(
        public readonly User $user,
        public readonly Subscription $subscription,
        public readonly string $oldPlanPriceId,
        public readonly string $newPlanPriceId
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Plan Yükseltildi',
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
            markdown: 'mail.tenant.account.plan-upgraded',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'oldPlanName' => $oldPlanPrice?->plan?->name ?? 'Eski Plan',
                'newPlanName' => $newPlanPrice?->plan?->name ?? 'Yeni Plan',
                'newPrice' => number_format((float) ($newPlanPrice?->price ?? 0), 2),
                'currency' => $newPlanPrice?->currency ?? CurrencyHelper::defaultCode(),
                'dashboardUrl' => url('/app/dashboard'),
            ],
        );
    }
}
