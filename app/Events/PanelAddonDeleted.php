<?php

/**
 * Panel Addon Deleted Event
 *
 * This event is dispatched when an addon is deleted in the panel.
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

use App\Models\Addon;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an addon is deleted.
 *
 * Contains information about the addon and the admin who deleted it.
 */
class PanelAddonDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Addon $addon The addon that was deleted
     * @param User $user The admin who deleted the addon
     * @param string $ip IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Addon $addon,
        public readonly User $user,
        public readonly string $ip,
        public readonly string $userAgent
    ) {}
}
