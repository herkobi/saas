<?php

/**
 * Payment Failed Notification
 *
 * Notification sent when a payment fails.
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

use App\Mail\App\Account\PaymentFailedMail;
use App\Models\Checkout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for failed payment.
 *
 * Sends database and email notifications to the tenant owner.
 */
class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Checkout $checkout The checkout session
     */
    public function __construct(
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
            'type' => 'payment.failed',
            'title' => 'Ödeme Başarısız',
            'message' => 'Ödeme işleminiz tamamlanamadı. Lütfen tekrar deneyin.',
            'checkout_id' => $this->checkout->id,
            'amount' => $this->checkout->final_amount,
            'currency' => $this->checkout->currency,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return PaymentFailedMail
     */
    public function toMail(mixed $notifiable): PaymentFailedMail
    {
        return new PaymentFailedMail($notifiable, $this->checkout);
    }
}
