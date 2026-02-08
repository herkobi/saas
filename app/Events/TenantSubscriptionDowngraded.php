<?php

/**
 * Subscription Downgraded Event
 *
 * This event is dispatched when a subscription is downgraded
 * or scheduled to be downgraded at period end.
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

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a subscription is downgraded.
 *
 * Contains subscription details and whether the change was applied immediately.
 */
class TenantSubscriptionDowngraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The subscription
     * @param string $oldPlanPriceId The previous plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change was applied immediately
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $oldPlanPriceId,
        public readonly string $newPlanPriceId,
        public readonly bool $immediate
    ) {}
}
