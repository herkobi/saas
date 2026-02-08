<?php

/**
 * Trial Ending Mail
 *
 * This mailable is sent when a subscription's trial period is about to end.
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

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for trial ending warning notifications.
 */
class TrialEndingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user to notify
     * @param Subscription $subscription The subscription with ending trial
     * @param int $daysRemaining Days until trial ends
     */
    public function __construct(
        public readonly User $user,
        public readonly Subscription $subscription,
        public readonly int $daysRemaining
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Deneme Süresi Bitiyor - {$this->daysRemaining} Gün Kaldı",
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        $this->subscription->load('price.plan');

        return new Content(
            markdown: 'mail.tenant.account.trial-ending',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
                'planName' => $this->subscription->price->plan->name ?? 'Plan',
                'trialEndsAt' => $this->subscription->trial_ends_at?->format('d.m.Y'),
                'subscriptionUrl' => url('/app/subscription'),
            ],
        );
    }
}
