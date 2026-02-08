<?php

/**
 * Tenant Password Changed Event
 *
 * This event is dispatched when a tenant user's password is successfully changed.
 * It carries information about the user, tenant context, and request context
 * for auditing purposes.
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
 * Event dispatched when a tenant user's password is changed.
 *
 * Contains information about the user, tenant context, and request context
 * for auditing and notification purposes.
 */
class TenantPasswordChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The user who changed their password
     * @param Tenant $tenant The tenant context in which the change occurred
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
