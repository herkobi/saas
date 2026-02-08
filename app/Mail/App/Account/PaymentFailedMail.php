<?php

/**
 * Payment Failed Mail
 *
 * This mailable is sent when a payment fails.
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

use App\Models\Checkout;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for failed payment notifications.
 *
 * Sends an email informing the user about the payment failure.
 */
class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user receiving the notification
     * @param Checkout $checkout The checkout session
     */
    public function __construct(
        public readonly User $user,
        public readonly Checkout $checkout
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ödeme Başarısız',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        $this->checkout->load('planPrice.plan');

        return new Content(
            markdown: 'mail.tenant.account.payment-failed',
            with: [
                'user' => $this->user,
                'checkout' => $this->checkout,
                'planName' => $this->checkout->planPrice->plan->name ?? 'Plan',
                'amount' => number_format((float) $this->checkout->final_amount, 2),
                'currency' => $this->checkout->currency,
                'retryUrl' => url('/app/checkout/' . $this->checkout->plan_price_id . '/' . $this->checkout->type),
            ],
        );
    }
}
