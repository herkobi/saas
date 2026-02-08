<?php

/**
 * Tenant Subscription Expired Event
 *
 * This event is dispatched when a tenant's subscription expires
 * and access should be restricted or revoked.
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
 * Event dispatched when a tenant subscription expires.
 */
class TenantSubscriptionExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The expired subscription
     */
    public function __construct(
        public readonly Subscription $subscription
    ) {}
}
