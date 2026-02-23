<?php

/**
 * PayTR Service
 *
 * This service implements the PayTR payment gateway integration.
 * It handles token generation, callback verification, and refund operations.
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

use App\Helpers\CurrencyHelper;
use App\Models\Checkout;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * PayTR payment gateway service implementation.
 *
 * Provides methods for creating payment tokens, verifying callbacks,
 * and processing refunds through the PayTR payment gateway.
 */
class PayTRService
{
    /**
     * PayTR merchant ID.
     *
     * @var string
     */
    protected string $merchantId;

    /**
     * PayTR merchant key.
     *
     * @var string
     */
    protected string $merchantKey;

    /**
     * PayTR merchant salt.
     *
     * @var string
     */
    protected string $merchantSalt;

    /**
     * Create a new PayTR service instance.
     */
    public function __construct()
    {
        $this->merchantId = config('paytr.merchant_id', '');
        $this->merchantKey = config('paytr.merchant_key', '');
        $this->merchantSalt = config('paytr.merchant_salt', '');

        if ($this->merchantId === '' || $this->merchantKey === '' || $this->merchantSalt === '') {
            throw new \RuntimeException(
                'PayTR credentials are not configured. Check PAYTR_MERCHANT_ID, PAYTR_MERCHANT_KEY, PAYTR_MERCHANT_SALT in .env'
            );
        }
    }

    /**
     * Create a payment token for iframe integration.
     *
     * @param Checkout $checkout The checkout session
     * @param array<string, mixed> $userData User information for payment form
     * @return array{success: bool, token?: string, error?: string}
     */
    public function createPaymentToken(Checkout $checkout, array $userData): array
    {
        $basketItems = $this->prepareBasketItems($checkout);
        $paymentAmount = (int) ($checkout->final_amount * 100);

        $hashStr = $this->merchantId
            . $userData['user_ip']
            . $checkout->merchant_oid
            . $userData['email']
            . $paymentAmount
            . $basketItems
            . config('paytr.no_installment')
            . config('paytr.max_installment')
            . CurrencyHelper::defaultCode()
            . ($this->isTestMode() ? '1' : '0');

        $paytrToken = $this->generateHash($hashStr);

        $postData = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $userData['user_ip'],
            'merchant_oid' => $checkout->merchant_oid,
            'email' => $userData['email'],
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $basketItems,
            'debug_on' => config('paytr.debug_on') ? 1 : 0,
            'no_installment' => config('paytr.no_installment'),
            'max_installment' => config('paytr.max_installment'),
            'user_name' => $userData['name'] ?? '',
            'user_address' => $userData['address'] ?? '',
            'user_phone' => $userData['phone'] ?? '',
            'merchant_ok_url' => config('paytr.success_url'),
            'merchant_fail_url' => config('paytr.fail_url'),
            'timeout_limit' => config('paytr.timeout_limit'),
            'currency' => CurrencyHelper::defaultCode(),
            'test_mode' => $this->isTestMode() ? '1' : '0',
            'lang' => config('paytr.lang'),
        ];

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post(config('paytr.api_url'), $postData);
        } catch (\Throwable) {
            return [
                'success' => false,
                'error' => 'Ödeme servisine bağlanılamadı. Lütfen tekrar deneyin.',
            ];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'PayTR API isteği başarısız oldu.',
            ];
        }

        $result = $response->json();

        if (($result['status'] ?? '') !== 'success') {
            return [
                'success' => false,
                'error' => 'Ödeme işlemi başlatılamadı. Lütfen tekrar deneyin.',
            ];
        }

        return [
            'success' => true,
            'token' => $result['token'],
        ];
    }

    /**
     * Verify the callback hash from the payment gateway.
     *
     * @param array<string, mixed> $payload The callback payload
     * @return bool
     */
    public function verifyCallback(array $payload): bool
    {
        $hash = $payload['hash'] ?? '';
        $merchantOid = $payload['merchant_oid'] ?? '';
        $status = $payload['status'] ?? '';
        $totalAmount = $payload['total_amount'] ?? '';

        $hashStr = $merchantOid . $this->merchantSalt . $status . $totalAmount;
        $expectedHash = base64_encode(hash_hmac('sha256', $hashStr, $this->merchantKey, true));

        return hash_equals($expectedHash, $hash);
    }

    /**
     * Parse the callback payload and extract relevant data.
     *
     * @param array<string, mixed> $payload The callback payload
     * @return array{merchant_oid: string, status: string, total_amount: string, hash: string}
     */
    public function parseCallback(array $payload): array
    {
        return [
            'merchant_oid' => $payload['merchant_oid'] ?? '',
            'status' => $payload['status'] ?? '',
            'total_amount' => $payload['total_amount'] ?? '',
            'hash' => $payload['hash'] ?? '',
        ];
    }

    /**
     * Process a refund for a completed payment.
     *
     * @param string $merchantOid The merchant order ID
     * @param float $amount The amount to refund
     * @return array{success: bool, error?: string}
     */
    public function refund(string $merchantOid, float $amount): array
    {
        $refundAmount = (int) ($amount * 100);

        $hashStr = $this->merchantId . $merchantOid . $refundAmount;
        $paytrToken = $this->generateHash($hashStr);

        $postData = [
            'merchant_id' => $this->merchantId,
            'merchant_oid' => $merchantOid,
            'return_amount' => $refundAmount,
            'paytr_token' => $paytrToken,
        ];

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post(config('paytr.api_url') . '/iade', $postData);
        } catch (\Throwable) {
            return [
                'success' => false,
                'error' => 'İade servisi yanıt vermedi. Lütfen tekrar deneyin.',
            ];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'İade isteği başarısız oldu.',
            ];
        }

        $result = $response->json();

        if (($result['status'] ?? '') !== 'success') {
            return [
                'success' => false,
                'error' => 'İade işlemi gerçekleştirilemedi. Lütfen tekrar deneyin.',
            ];
        }

        return ['success' => true];
    }

    /**
     * Get the iframe URL for payment processing.
     *
     * @param string $token The payment token
     * @return string
     */
    public function getIframeUrl(string $token): string
    {
        return config('paytr.iframe_url') . $token;
    }

    /**
     * Check if the gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        return (bool) config('paytr.test_mode', true);
    }

    /**
     * Prepare basket items for PayTR.
     *
     * @param Checkout $checkout The checkout session
     * @return string Base64 encoded basket items
     */
    protected function prepareBasketItems(Checkout $checkout): string
    {
        $checkout->load('planPrice.plan');

        $planName = $checkout->planPrice->plan->name ?? 'Abonelik';
        $amount = number_format((float) $checkout->final_amount, 2, '.', '');

        $basket = [
            [$planName, $amount, 1],
        ];

        return base64_encode(json_encode($basket));
    }

    /**
     * Generate HMAC hash for PayTR.
     *
     * @param string $data The data to hash
     * @return string
     */
    protected function generateHash(string $data): string
    {
        return base64_encode(
            hash_hmac('sha256', $data . $this->merchantSalt, $this->merchantKey, true)
        );
    }
}
