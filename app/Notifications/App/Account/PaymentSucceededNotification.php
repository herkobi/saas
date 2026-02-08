<?php

/**
 * Payment Succeeded Notification
 *
 * Notification sent when a payment is successfully processed.
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

use App\Mail\App\Account\PaymentSucceededMail;
use App\Models\Checkout;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for successful payment.
 *
 * Sends database and email notifications to the tenant owner.
 */
class PaymentSucceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Payment $payment The payment record
     * @param Checkout $checkout The checkout session
     */
    public function __construct(
        private readonly Payment $payment,
        private readonly Checkout $checkout
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
        return [
            'tenant_id' => $this->checkout->tenant_id,
            'type' => 'payment.succeeded',
            'title' => 'Ödeme Başarılı',
            'message' => sprintf(
                'Ödemeniz başarıyla tamamlandı. Tutar: %s %s',
                number_format((float) $this->payment->amount, 2),
                $this->payment->currency
            ),
            'payment_id' => $this->payment->id,
            'checkout_id' => $this->checkout->id,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return PaymentSucceededMail
     */
    public function toMail(mixed $notifiable): PaymentSucceededMail
    {
        return new PaymentSucceededMail($notifiable, $this->payment, $this->checkout);
    }
}
