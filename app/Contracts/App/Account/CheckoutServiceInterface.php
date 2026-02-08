<?php

/**
 * Checkout Service Interface Contract
 *
 * This interface defines the contract for checkout service implementations.
 * It provides methods for initiating, processing, and managing checkout sessions.
 *
 * @package    App\Contracts\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Enums\CheckoutType;
use App\Models\Addon;
use App\Models\Checkout;
use App\Models\PlanPrice;
use App\Models\Tenant;

/**
 * Interface for checkout service implementations.
 *
 * Defines the contract for managing checkout sessions including
 * initiation, payment token generation, and callback processing.
 */
interface CheckoutServiceInterface
{
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
    ): Checkout;

    /**
     * Generate payment token for checkout.
     *
     * @param Checkout $checkout The checkout session
     * @param array<string, mixed> $userData User data for payment form
     * @return array{success: bool, token?: string, iframe_url?: string, error?: string}
     */
    public function generatePaymentToken(Checkout $checkout, array $userData): array;

    /**
     * Process the payment callback.
     *
     * @param array<string, mixed> $payload The callback payload from payment gateway
     * @return array{success: bool, checkout?: Checkout, error?: string}
     */
    public function processCallback(array $payload): array;

    /**
     * Get a checkout by merchant order ID.
     *
     * @param string $merchantOid The merchant order ID
     * @return Checkout|null
     */
    public function getByMerchantOid(string $merchantOid): ?Checkout;

    /**
     * Cancel a pending checkout.
     *
     * @param Checkout $checkout The checkout to cancel
     * @return void
     */
    public function cancel(Checkout $checkout): void;

    /**
     * Expire old pending checkouts.
     *
     * @return int Number of checkouts expired
     */
    public function expireOldCheckouts(): int;

    /**
     * Calculate the checkout amount with proration.
     *
     * @param Tenant $tenant The tenant
     * @param PlanPrice|Addon $item The plan price or addon
     * @param CheckoutType $type The checkout type
     * @param int $quantity The quantity (for addons)
     * @return array{subtotal: float, tax_rate: float, tax_amount: float, final_amount: float}
     */
    public function calculateAmount(Tenant $tenant, PlanPrice|Addon $item, CheckoutType $type, int $quantity = 1): array;
}
