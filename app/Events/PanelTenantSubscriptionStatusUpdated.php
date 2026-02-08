<?php

/**
 * Subscription Status Updated By Admin Event
 *
 * This event is dispatched when a subscription status is manually updated
 * by an admin user from the panel interface.
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

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when admin manually updates subscription status.
 *
 * Contains subscription, old/new status, admin user, and audit context
 * information for logging and notification purposes.
 */
class PanelTenantSubscriptionStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The subscription
     * @param SubscriptionStatus $oldStatus The previous status
     * @param SubscriptionStatus $newStatus The new status
     * @param string|null $reason Optional reason for the change
     * @param User $admin The admin user who updated the status
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly SubscriptionStatus $oldStatus,
        public readonly SubscriptionStatus $newStatus,
        public readonly ?string $reason,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
