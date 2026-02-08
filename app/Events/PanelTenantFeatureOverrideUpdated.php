<?php

/**
 * Tenant Feature Override Updated Event
 *
 * This event is dispatched when tenant feature overrides are updated
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

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when admin updates tenant feature overrides.
 *
 * Contains tenant, old and new overrides, action type, admin user,
 * and audit context information for logging purposes.
 */
class PanelTenantFeatureOverrideUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant The tenant whose overrides were updated
     * @param array<string, string> $oldOverrides The previous override values
     * @param array<string, string> $newOverrides The new override values
     * @param string $action The action performed (sync, remove, clear)
     * @param User $admin The admin user who performed the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Tenant $tenant,
        public readonly array $oldOverrides,
        public readonly array $newOverrides,
        public readonly string $action,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
