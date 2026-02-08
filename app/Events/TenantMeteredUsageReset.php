<?php

/**
 * Tenant Metered Usage Reset Event
 *
 * This event is dispatched when a tenant's metered feature usage
 * is reset at the start of a new billing cycle.
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

use App\Models\TenantUsage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when tenant metered usage is reset.
 */
class TenantMeteredUsageReset
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param TenantUsage $usage The reset usage record
     * @param float $previousUsed The previous usage amount before reset
     */
    public function __construct(
        public readonly TenantUsage $usage,
        public readonly float $previousUsed
    ) {}
}
