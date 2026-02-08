<?php

declare(strict_types=1);

namespace App\Helpers;

final class TaxHelper
{
    /**
     * config/herkobi.php üzerinden vergi oranını döndürür.
     */
    public static function rate(): float
    {
        return (float) config('herkobi.payment.tax_rate', 20);
    }

    /**
     * Vergi oranını ondalık biçimde döndürür (Örn: 0.20).
     */
    public static function decimalRate(): float
    {
        return self::rate() / 100;
    }

    /**
     * Verilen tutarın vergi miktarını hesaplar.
     */
    public static function calculateAmount(float $amount): float
    {
        return round($amount * (self::rate() / 100), 2);
    }

    /**
     * Tutara vergi ekler (KDV Dahil hesaplar).
     */
    public static function addToAmount(float $amount): float
    {
        return round($amount + self::calculateAmount($amount), 2);
    }

    /**
     * Vergi dahil tutardan vergisiz ana parayı bulur.
     */
    public static function subtractFromAmount(float $amountWithTax): float
    {
        return round($amountWithTax / (1 + (self::rate() / 100)), 2);
    }
}
