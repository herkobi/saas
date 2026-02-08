<?php

/**
 * Subscription Upgraded Event
 *
 * This event is dispatched when a subscription is upgraded to a higher plan.
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
 * Event dispatched when a subscription is upgraded.
 *
 * Contains subscription details and plan price IDs for listeners.
 */
class TenantSubscriptionUpgraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The upgraded subscription
     * @param string $oldPlanPriceId The previous plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param Checkout $checkout The checkout session
     * @param Payment $payment The payment record
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $oldPlanPriceId,
        public readonly string $newPlanPriceId,
        public readonly ?Checkout $checkout = null,
        public readonly ?Payment $payment = null
    ) {}
}
