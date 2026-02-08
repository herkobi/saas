<?php

/**
 * User Status Updated By Admin Event
 *
 * This event is dispatched when a user status is manually updated
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

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when admin manually updates user status.
 *
 * Contains user, old/new status, admin user, and audit context
 * information for logging and notification purposes.
 */
class PanelUserStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The user whose status was updated
     * @param UserStatus $oldStatus The previous status
     * @param UserStatus $newStatus The new status
     * @param string|null $reason Optional reason for the change
     * @param User $admin The admin user who updated the status
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly User $user,
        public readonly UserStatus $oldStatus,
        public readonly UserStatus $newStatus,
        public readonly ?string $reason,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
