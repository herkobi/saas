<?php

/**
 * Tenant Process Successful Payment Listener
 *
 * This listener handles subscription creation/update after a successful payment.
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

use App\Services\App\Account\AddonPurchaseService;
use App\Services\App\Account\PlanChangeService;
use App\Services\App\Account\SubscriptionPurchaseService;
use App\Enums\CheckoutType;
use App\Events\TenantPaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener for processing successful payments.
 *
 * Creates or updates subscriptions based on checkout type.
 */
class TenantProcessSuccessfulPayment implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'payments';

    /**
     * Create a new listener instance.
     *
     * @param SubscriptionPurchaseService $purchaseService
     * @param PlanChangeService $planChangeService
     * @param AddonPurchaseService $addonPurchaseService
     */
    public function __construct(
        protected SubscriptionPurchaseService $purchaseService,
        protected PlanChangeService $planChangeService,
        protected AddonPurchaseService $addonPurchaseService
    ) {}

    /**
     * Handle the event.
     *
     * @param TenantPaymentSucceeded $event
     * @return void
     */
    public function handle(TenantPaymentSucceeded $event): void
    {
        $checkout = $event->checkout;
        $payment = $event->payment->fresh();

        if (in_array($checkout->type, [CheckoutType::ADDON, CheckoutType::ADDON_RENEW], true)) {
            if ($payment->addon_id !== null) {
                return;
            }

            $this->addonPurchaseService->processCheckout($checkout, $payment);

            return;
        }

        if ($payment->subscription_id !== null) {
            return;
        }

        match ($checkout->type) {
            CheckoutType::UPGRADE => $this->planChangeService->processUpgrade($checkout, $payment),
            default => $this->purchaseService->processCheckout($checkout, $payment),
        };
    }
}
