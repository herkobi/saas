<?php

/**
 * Payment Marked As Invoiced Event
 *
 * This event is dispatched when a payment is marked as invoiced.
 *
 * @package    App\Events
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Events;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a payment is marked as invoiced.
 *
 * Contains information about the payment and the admin who marked it.
 */
class PanelPaymentMarkedAsInvoiced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment The payment that was marked as invoiced
     * @param User $markedBy The admin who marked the payment
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Payment $payment,
        public readonly User $markedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
