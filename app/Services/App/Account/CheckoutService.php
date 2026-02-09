<?php

/**
 * Checkout Service
 *
 * This service handles checkout session management including
 * initiation, payment token generation, and callback processing.
 *
 * @package    App\Services\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Contracts\App\Account\CheckoutServiceInterface;
use App\Contracts\App\Account\PaymentGatewayInterface;
use App\Contracts\App\Account\ProrationServiceInterface;
use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
use App\Enums\PaymentStatus;
use App\Events\TenantCheckoutInitiated;
use App\Events\TenantPaymentFailed;
use App\Events\TenantPaymentSucceeded;
use App\Helpers\CurrencyHelper;
use App\Helpers\PaymentHelper;
use App\Helpers\TaxHelper;
use App\Models\Addon;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing checkout sessions.
 *
 * Provides methods for initiating checkouts, generating payment tokens,
 * processing callbacks, and managing checkout lifecycle.
 */
class CheckoutService implements CheckoutServiceInterface
{
    /**
     * Create a new checkout service instance.
     *
     * @param PaymentGatewayInterface $gateway The payment gateway
     * @param ProrationServiceInterface $prorationService The proration service
     */
    public function __construct(
        protected PaymentGatewayInterface $gateway,
        protected ProrationServiceInterface $prorationService
    ) {}

    /**
     * Initiate a new checkout session.
     *
     * @param Tenant $tenant The tenant initiating checkout
     * @param PlanPrice $planPrice The plan price to purchase
     * @param CheckoutType $type The checkout type (new, renew, upgrade, downgrade)
     * @param array<string, mixed> $billingInfo Billing information
     * @return Checkout
     */
    public function initiate(
        Tenant $tenant,
        PlanPrice $planPrice,
        CheckoutType $type,
        array $billingInfo
    ): Checkout {
        $amounts = $this->calculateAmount($tenant, $planPrice, $type);

        $checkout = Checkout::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $planPrice->id,
            'merchant_oid' => Checkout::generateMerchantOid($tenant->id),
            'type' => $type,
            'status' => CheckoutStatus::PENDING,
            'amount' => $amounts['subtotal'],
            'proration_credit' => $amounts['proration_credit'] ?? 0,
            'final_amount' => $amounts['final_amount'],
            'currency' => $amounts['currency'] ?? CurrencyHelper::defaultCode(),
            'billing_info' => $billingInfo,
            'expires_at' => now()->addMinutes(PaymentHelper::sessionTimeout()),
        ]);

        TenantCheckoutInitiated::dispatch($checkout);

        return $checkout->load('planPrice.plan');
    }

    /**
     * Generate payment token for checkout.
     *
     * @param Checkout $checkout The checkout session
     * @param array<string, mixed> $userData User data for payment form
     * @return array{success: bool, token?: string, iframe_url?: string, error?: string}
     */
    public function generatePaymentToken(Checkout $checkout, array $userData): array
    {
        if (!$checkout->canProcess()) {
            return [
                'success' => false,
                'error' => 'Checkout işlenemez durumda.',
            ];
        }

        try {
            $result = $this->gateway->createPaymentToken($checkout, $userData);

            if (!$result['success']) {
                return $result;
            }

            $checkout->markAsProcessing($result['token']);

            return [
                'success' => true,
                'token' => $result['token'],
                'iframe_url' => $this->gateway->getIframeUrl($result['token']),
            ];
        } catch (\Throwable) {
            return [
                'success' => false,
                'error' => 'Ödeme başlatılırken bir hata oluştu. Lütfen tekrar deneyin.',
            ];
        }
    }

    /**
     * Process the payment callback.
     *
     * @param array<string, mixed> $payload The callback payload from payment gateway
     * @return array{success: bool, checkout?: Checkout, error?: string}
     */
    public function processCallback(array $payload): array
    {
        if (!$this->gateway->verifyCallback($payload)) {
            return [
                'success' => false,
                'error' => 'Hash doğrulaması başarısız.',
            ];
        }

        $data = $this->gateway->parseCallback($payload);

        return DB::transaction(function () use ($data) {
            $checkout = Checkout::where('merchant_oid', $data['merchant_oid'])
                ->lockForUpdate()
                ->first();

            if (!$checkout) {
                return [
                    'success' => false,
                    'error' => 'Checkout bulunamadı.',
                ];
            }

            if ($checkout->status->isFinal()) {
                return [
                    'success' => true,
                    'checkout' => $checkout,
                ];
            }

            $isSuccess = $data['status'] === 'success';

            if ($isSuccess) {
                $payment = $this->createPayment($checkout, $data);
                $checkout->markAsCompleted($payment->id);

                TenantPaymentSucceeded::dispatch($checkout, $payment);
            } else {
                $checkout->markAsFailed();

                TenantPaymentFailed::dispatch($checkout, $data);
            }

            return [
                'success' => true,
                'checkout' => $checkout->fresh(),
            ];
        });
    }

    /**
     * Get a checkout by merchant order ID.
     *
     * @param string $merchantOid The merchant order ID
     * @return Checkout|null
     */
    public function getByMerchantOid(string $merchantOid): ?Checkout
    {
        return Checkout::where('merchant_oid', $merchantOid)->first();
    }

    /**
     * Cancel a pending checkout.
     *
     * @param Checkout $checkout The checkout to cancel
     * @return void
     */
    public function cancel(Checkout $checkout): void
    {
        if ($checkout->status->canCancel()) {
            $checkout->markAsCancelled();
        }
    }

    /**
     * Expire old pending checkouts.
     *
     * @return int Number of checkouts expired
     */
    public function expireOldCheckouts(): int
    {
        return (int) Checkout::whereIn('status', [CheckoutStatus::PENDING->value, CheckoutStatus::PROCESSING->value])
            ->where('expires_at', '<', now())
            ->update(['status' => CheckoutStatus::EXPIRED->value]);
    }

    /**
     * Calculate the checkout amount with proration.
     *
     * @param Tenant $tenant The tenant
     * @param PlanPrice|Addon $item The plan price or addon
     * @param CheckoutType $type The checkout type
     * @param int $quantity The quantity (for addons)
     * @return array{subtotal: float, tax_rate: float, tax_amount: float, total: float, proration_credit: float, final_amount: float}
     */
    public function calculateAmount(
        Tenant $tenant,
        PlanPrice|Addon $item,
        CheckoutType $type,
        int $quantity = 1
    ): array {
        // Determine if this is an addon or plan
        $isAddon = $item instanceof Addon;

        if ($isAddon) {
            return $this->calculateAddonAmount($item, $quantity);
        }

        return $this->calculatePlanAmount($tenant, $item, $type);
    }

    protected function calculateAddonAmount(Addon $addon, int $quantity): array
    {
        $unitPrice = (float) $addon->price;
        $subtotal = $unitPrice * $quantity;

        $tax = TaxHelper::calculateAmount($subtotal);
        $finalAmount = $subtotal + $tax;

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => round($subtotal, 2),
            'tax_rate' => TaxHelper::rate(),
            'tax_name' => config('herkobi.payment.tax_name', 'KDV'),
            'tax' => $tax,
            'final_amount' => round($finalAmount, 2),
            'currency' => $addon->currency,
        ];
    }

    protected function calculatePlanAmount(Tenant $tenant, PlanPrice $planPrice, CheckoutType $type): array
    {
        $price = (float) $planPrice->price;
        $prorationCredit = 0.0;

        if (in_array($type, [CheckoutType::UPGRADE, CheckoutType::DOWNGRADE], true) && $tenant->subscription) {
            $planPrice->loadMissing('plan');
            $prorationType = $type === CheckoutType::UPGRADE
                ? $planPrice->plan->resolveUpgradeProration()
                : $planPrice->plan->resolveDowngradeProration();

            $proration = $this->prorationService->calculate($tenant->subscription, $planPrice, $prorationType);
            $price = $proration['final_amount'];
            $prorationCredit = $proration['credit'];
        }

        $tax = TaxHelper::calculateAmount($price);
        $finalAmount = $price + $tax;

        return [
            'subtotal' => round($price, 2),
            'tax_rate' => TaxHelper::rate(),
            'tax_name' => config('herkobi.payment.tax_name', 'KDV'),
            'tax' => $tax,
            'proration_credit' => round($prorationCredit, 2),
            'final_amount' => round($finalAmount, 2),
            'currency' => $planPrice->currency,
        ];
    }

    /**
     * Create a payment record for a successful checkout.
     *
     * @param Checkout $checkout The checkout
     * @param array<string, mixed> $callbackData The callback data
     * @return Payment
     */
    protected function createPayment(Checkout $checkout, array $callbackData): Payment
    {
        return Payment::create([
            'tenant_id' => $checkout->tenant_id,
            'gateway' => 'paytr',
            'gateway_payment_id' => $callbackData['merchant_oid'],
            'amount' => $checkout->final_amount,
            'currency' => $checkout->currency,
            'status' => PaymentStatus::COMPLETED,
            'description' => $this->getPaymentDescription($checkout),
            'gateway_response' => $callbackData,
            'metadata' => array_filter([
                'checkout_id' => $checkout->id,
                'checkout_type' => $checkout->type,
                'plan_price_id' => $checkout->plan_price_id,
                'addon_id' => $checkout->addon_id,
                'proration_credit' => $checkout->proration_credit,
            ]),
            'paid_at' => now(),
        ]);
    }

    /**
     * Get the payment description based on checkout type.
     *
     * @param Checkout $checkout The checkout
     * @return string
     */
    protected function getPaymentDescription(Checkout $checkout): string
    {
        if ($checkout->type === CheckoutType::ADDON) {
            $checkout->load('addon');
            $addonName = $checkout->addon->name ?? 'Eklenti';

            return "{$addonName} - {$checkout->type->label()}";
        }

        $checkout->load('planPrice.plan');
        $planName = $checkout->planPrice->plan->name ?? 'Plan';

        return "{$planName} - {$checkout->type->label()}";
    }
}
