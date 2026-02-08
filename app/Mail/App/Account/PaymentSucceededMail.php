<?php

/**
 * Payment Succeeded Mail
 *
 * This mailable is sent when a payment is successfully processed.
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
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for successful payment notifications.
 *
 * Sends an email with payment details and receipt information.
 */
class PaymentSucceededMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user receiving the notification
     * @param Payment $payment The payment record
     * @param Checkout $checkout The checkout session
     */
    public function __construct(
        public readonly User $user,
        public readonly Payment $payment,
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
            subject: 'Ödeme Başarılı',
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
            markdown: 'mail.tenant.account.payment-succeeded',
            with: [
                'user' => $this->user,
                'payment' => $this->payment,
                'checkout' => $this->checkout,
                'planName' => $this->checkout->planPrice->plan->name ?? 'Plan',
                'amount' => number_format((float) $this->payment->amount, 2),
                'currency' => $this->payment->currency,
                'paidAt' => $this->payment->paid_at?->format('d.m.Y H:i'),
                'dashboardUrl' => url('/app/dashboard'),
            ],
        );
    }
}
