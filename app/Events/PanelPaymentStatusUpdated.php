<?php

/**
 * Payment Status Updated Event
 *
 * This event is dispatched when a payment's status is updated.
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

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a payment status is updated.
 *
 * Contains information about the payment, old status, and the admin who updated it.
 */
class PanelPaymentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment The payment that was updated
     * @param PaymentStatus $oldStatus The previous status
     * @param User $updatedBy The admin who updated the status
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     */
    public function __construct(
        public readonly Payment $payment,
        public readonly PaymentStatus $oldStatus,
        public readonly User $updatedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
