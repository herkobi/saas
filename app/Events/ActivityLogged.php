<?php

/**
 * Activity Logged Event
 *
 * This event is dispatched when a user activity is logged in the panel.
 * It carries information about the user, activity, and request context.
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

use App\Models\Activity;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when user activity is logged.
 *
 * This event is fired whenever a user activity is successfully logged
 * in the panel system, providing context for potential listeners.
 * Note: This event carries full model objects as it's not queued.
 */
class ActivityLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The user who performed the action
     * @param Activity $activity The activity log that was created
     * @param string $ipAddress The IP address from which the action was performed
     * @param string $userAgent The user agent from which the action was performed
     */
    public function __construct(
        public readonly User $user,
        public readonly Activity $activity,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
