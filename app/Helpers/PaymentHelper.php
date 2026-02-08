<?php

declare(strict_types=1);

namespace App\Helpers;

final class PaymentHelper
{
    /**
     * Varsayılan ülke kodunu döndürür.
     */
    public static function country(): string
    {
        return (string) config('herkobi.payment.country', 'TR');
    }

    /**
     * Ödeme sistemine (PayTR vb.) gönderilecek kuruş formatını hazırlar.
     * Örn: 10.50 -> 1050
     */
    public static function formatForGateway(float $amount): int
    {
        return (int) (round($amount, 2) * 100);
    }

    /**
     * Checkout seans süresini döndürür.
     */
    public static function sessionTimeout(): int
    {
        return (int) config('herkobi.subscription.checkout_expiry_minutes', 30);
    }
}
