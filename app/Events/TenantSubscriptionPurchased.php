<?php

/**
 * Subscription Purchased Event
 *
 * This event is dispatched when a new subscription is purchased.
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
 * Event dispatched when a subscription is purchased.
 *
 * Contains the subscription, checkout, and payment models for listeners.
 */
class TenantSubscriptionPurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The created subscription
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly Checkout $checkout,
        public readonly Payment $payment
    ) {}
}
