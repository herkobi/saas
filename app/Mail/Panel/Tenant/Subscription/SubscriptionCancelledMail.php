<?php

/**
 * Subscription Cancelled Mail
 *
 * This mailable is sent to tenant owners when their subscription
 * is cancelled by an admin user from the panel.
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

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for subscription cancellation notifications.
 *
 * Sends an email to the tenant owner informing them about
 * the subscription cancellation with relevant details.
 */
class SubscriptionCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The tenant owner receiving the notification
     * @param Subscription $subscription The cancelled subscription
     * @param bool $immediate Whether the cancellation is immediate
     */
    public function __construct(
        public readonly User $user,
        public readonly Subscription $subscription,
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
            subject: 'Aboneliğiniz İptal Edildi',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.panel.tenant.subscription.cancelled',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'planName' => $this->subscription->price?->plan?->name ?? 'Bilinmeyen Plan',
                'immediate' => $this->immediate,
                'endsAt' => $this->subscription->ends_at?->format('d.m.Y H:i'),
                'accountUrl' => url('/app/subscription'),
            ],
        );
    }
}
