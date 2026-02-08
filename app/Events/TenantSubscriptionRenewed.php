<?php

/**
 * Subscription Renewed Event
 *
 * This event is dispatched when a subscription is renewed.
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
use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a subscription is renewed.
 *
 * Contains the subscription, checkout, and payment models for listeners.
 */
class TenantSubscriptionRenewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The renewed subscription
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly Checkout $checkout,
        public readonly Payment $payment
    ) {}
}
