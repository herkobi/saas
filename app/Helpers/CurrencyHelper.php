<?php

declare(strict_types=1);

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Varsayılan para birimi kodunu döndürür (Örn: TRY).
     */
    public static function defaultCode(): string
    {
        return (string) config('herkobi.payment.currency', 'TRY');
    }

    /**
     * PayTR gibi sistemlerin beklediği formatı döndürür (TRY -> TL).
     */
    public static function gatewayCode(): string
    {
        $code = self::defaultCode();
        return $code === 'TRY' ? 'TL' : $code;
    }

    /**
     * Para birimi sembolünü döndürür (Örn: ₺).
     */
    public static function symbol(): string
    {
        return (string) config('herkobi.payment.currency_symbol', '₺');
    }

    /**
     * Parayı sembol ve format ile birlikte gösterir (Örn: ₺1.250,00).
     */
    public static function format(float|string $amount): string
    {
        $formatted = number_format((float) $amount, 2, ',', '.');
        return self::symbol() . $formatted;
    }
}
