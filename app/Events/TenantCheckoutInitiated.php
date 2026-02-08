<?php

/**
 * Checkout Initiated Event
 *
 * This event is dispatched when a new checkout session is initiated.
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

use App\Models\Checkout;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a checkout session is initiated.
 *
 * Contains the checkout model for listeners to process.
 */
class TenantCheckoutInitiated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Checkout $checkout The initiated checkout
     */
    public function __construct(
        public readonly Checkout $checkout
    ) {}
}
