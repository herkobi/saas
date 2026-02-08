<?php

/**
 * Payment Succeeded Event
 *
 * This event is dispatched when a payment is successfully processed.
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

use App\Models\Checkout;
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a payment succeeds.
 *
 * Contains the checkout and payment models for listeners to process.
 */
class TenantPaymentSucceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Checkout $checkout The completed checkout
     * @param Payment $payment The payment record
     */
    public function __construct(
        public readonly Checkout $checkout,
        public readonly Payment $payment
    ) {}
}
