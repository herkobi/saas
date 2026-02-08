<?php

/**
 * Tenant User Role Changed Event
 *
 * This event is dispatched when a user's role is changed within a tenant.
 * It triggers notifications and activity logging.
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
 * Event dispatched when a tenant user's role is changed.
 */
class TenantUserRoleChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant The tenant context
     * @param User $user The user whose role was changed
     * @param int|null $oldRole The previous role value
     * @param int $newRole The new role value
     * @param User $changedBy The user who made the change
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $user,
        public readonly ?int $oldRole,
        public readonly int $newRole,
        public readonly User $changedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
