<?php

/**
 * Payment Failed Event
 *
 * This event is dispatched when a payment fails.
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
 * Event dispatched when a payment fails.
 *
 * Contains the checkout and failure data for listeners to process.
 */
class TenantPaymentFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Checkout $checkout The failed checkout
     * @param array<string, mixed> $failureData The failure data from gateway
     */
    public function __construct(
        public readonly Checkout $checkout,
        public readonly array $failureData = []
    ) {}
}
