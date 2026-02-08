<?php

/**
 * Tenant Usage Limit Reached Event
 *
 * This event is dispatched when a tenant reaches the usage limit
 * for a specific feature.
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

use App\Models\Feature;
use App\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a tenant reaches a feature usage limit.
 */
class TenantUsageLimitReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant The tenant that reached the limit
     * @param Feature $feature The feature that reached its limit
     * @param float $currentUsage The current usage amount
     * @param float $limit The limit that was reached
     */
    public function __construct(
        public readonly Tenant $tenant,
        public readonly Feature $feature,
        public readonly float $currentUsage,
        public readonly float $limit
    ) {}
}
