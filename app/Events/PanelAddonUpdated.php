<?php

/**
 * Panel Addon Updated Event
 *
 * This event is dispatched when an addon is updated in the panel.
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
 * Event dispatched when an addon is updated.
 *
 * Contains information about the addon and the admin who updated it.
 */
class PanelAddonUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Addon $addon The addon that was updated
     * @param User $user The admin who updated the addon
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
