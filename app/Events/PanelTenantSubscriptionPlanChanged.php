<?php

/**
 * Subscription Plan Changed By Admin Event
 *
 * This event is dispatched when a subscription plan is changed by an admin user
 * from the panel interface.
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
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when admin changes a subscription plan.
 *
 * Contains subscription, old and new plan price IDs, application type,
 * admin user, and audit context information for logging and notification purposes.
 */
class PanelTenantSubscriptionPlanChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The updated subscription
     * @param string $oldPlanPriceId The previous plan price ID
     * @param string $newPlanPriceId The new plan price ID
     * @param bool $immediate Whether the change is applied immediately
     * @param User $admin The panel user who changed the plan
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $oldPlanPriceId,
        public readonly string $newPlanPriceId,
        public readonly bool $immediate,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
