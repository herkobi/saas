<?php

/**
 * Tenant Updated By Admin Event
 *
 * This event is dispatched when a tenant is updated by an panel user
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

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when panel updates a tenant.
 *
 * Contains tenant, panel user, changes, and audit context information
 * for logging and notification purposes.
 */
class PanelTenantUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant The updated tenant
     * @param User $panel The panel user who performed the update
     * @param array<string, array{old: mixed, new: mixed}> $changes The changes made
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $admin,
        public readonly array $changes,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
