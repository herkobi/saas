<?php

/**
 * Log Payment Activity Listener
 *
 * This listener handles logging of payment related activities.
 * It listens for payment events and records them for auditing purposes.
 *
 * @package    App\Listeners
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Shared\ActivityService;
use App\Events\PanelPaymentMarkedAsInvoiced;
use App\Events\PanelPaymentStatusUpdated;

/**
 * Listener for logging payment activities.
 *
 * Records payment events with comprehensive audit information.
 */
class LogPaymentActivity
{
    /**
     * Create the event listener.
     *
     * @param ActivityService $activityService Service for logging activities
     */
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Handle payment status updated event.
     *
     * @param PanelPaymentStatusUpdated $event The event instance
     * @return void
     */
    public function handleStatusUpdated(PanelPaymentStatusUpdated $event): void
    {
        $this->activityService->log(
            user: $event->updatedBy,
            type: 'admin.payment_status_updated',
            description: 'Ödeme durumu güncellendi',
            log: [
                'payment_id' => $event->payment->id,
                'tenant_id' => $event->payment->tenant_id,
                'old_status' => $event->oldStatus->value,
                'new_status' => $event->payment->status->value,
                'amount' => $event->payment->amount,
                'updated_by_name' => $event->updatedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->payment->tenant_id
        );
    }

    /**
     * Handle payment marked as invoiced event.
     *
     * @param PanelPaymentMarkedAsInvoiced $event The event instance
     * @return void
     */
    public function handleMarkedAsInvoiced(PanelPaymentMarkedAsInvoiced $event): void
    {
        $this->activityService->log(
            user: $event->markedBy,
            type: 'admin.payment_invoiced',
            description: 'Ödeme faturalandırıldı olarak işaretlendi',
            log: [
                'payment_id' => $event->payment->id,
                'tenant_id' => $event->payment->tenant_id,
                'amount' => $event->payment->amount,
                'marked_by_name' => $event->markedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->payment->tenant_id
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events The event dispatcher
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            PanelPaymentStatusUpdated::class => 'handleStatusUpdated',
            PanelPaymentMarkedAsInvoiced::class => 'handleMarkedAsInvoiced',
        ];
    }
}
