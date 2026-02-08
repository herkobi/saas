<?php

/**
 * Subscription Cancelled By Admin Event
 *
 * This event is dispatched when a subscription is cancelled by an admin user
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
 * Event dispatched when admin cancels a subscription.
 *
 * Contains subscription, cancellation type, admin user, and audit context
 * information for logging and notification purposes.
 */
class PanelTenantSubscriptionCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The cancelled subscription
     * @param bool $immediate Whether the cancellation is immediate
     * @param User $admin The admin user who cancelled the subscription
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly bool $immediate,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
