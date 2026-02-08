<?php

/**
 * Tenant User Removed Event
 *
 * This event is dispatched when a user is removed from a tenant.
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
 * Event dispatched when a user is removed from a tenant.
 */
class TenantUserRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant The tenant context
     * @param User $user The user who was removed
     * @param int|null $role The user's role before removal
     * @param User $removedBy The user who performed the removal
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $user,
        public readonly ?int $role,
        public readonly User $removedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
