<?php

/**
 * Tenant Registered Event
 *
 * This event is dispatched when a new tenant account is successfully registered.
 * It carries information about the user, tenant, and request context
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
 * Event dispatched when a new tenant account is registered.
 *
 * Contains information about the user, tenant, and request context
 * for auditing and notification purposes.
 */
class TenantRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The user who registered
     * @param Tenant $tenant The new tenant account created
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
