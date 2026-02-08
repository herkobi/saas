<?php

/**
 * Payment Gateway Interface Contract
 *
 * This interface defines the contract for payment gateway implementations.
 * It provides a standardized API for payment processing operations.
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

use App\Models\Checkout;

/**
 * Interface for payment gateway implementations.
 *
 * Defines the contract for creating payment tokens, verifying callbacks,
 * and processing refunds through payment gateways.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment token for iframe integration.
     *
     * @param Checkout $checkout The checkout session
     * @param array<string, mixed> $userData User information for payment form
     * @return array{success: bool, token?: string, error?: string}
     */
    public function createPaymentToken(Checkout $checkout, array $userData): array;

    /**
     * Verify the callback hash from the payment gateway.
     *
     * @param array<string, mixed> $payload The callback payload
     * @return bool
     */
    public function verifyCallback(array $payload): bool;

    /**
     * Parse the callback payload and extract relevant data.
     *
     * @param array<string, mixed> $payload The callback payload
     * @return array{merchant_oid: string, status: string, total_amount: string, hash: string}
     */
    public function parseCallback(array $payload): array;

    /**
     * Process a refund for a completed payment.
     *
     * @param string $merchantOid The merchant order ID
     * @param float $amount The amount to refund
     * @return array{success: bool, error?: string}
     */
    public function refund(string $merchantOid, float $amount): array;

    /**
     * Get the iframe URL for payment processing.
     *
     * @param string $token The payment token
     * @return string
     */
    public function getIframeUrl(string $token): string;

    /**
     * Check if the gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool;
}
