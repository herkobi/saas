<?php

/**
 * Trial Ended Mail
 *
 * This mailable is sent when a tenant's trial period has ended.
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
 * Mailable for trial ended notifications.
 */
class TrialEndedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user to notify
     * @param Subscription $subscription The subscription with ended trial
     */
    public function __construct(
        public readonly User $user,
        public readonly Subscription $subscription
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deneme Süresi Sona Erdi',
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
            markdown: 'mail.tenant.account.trial-ended',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'planName' => $this->subscription->price->plan->name ?? 'Plan',
                'trialEndsAt' => $this->subscription->trial_ends_at?->format('d.m.Y'),
                'subscriptionUrl' => url('/app/subscription'),
            ],
        );
    }
}
