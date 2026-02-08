<?php

/**
 * PayTR Configuration
 *
 * Bu dosya Herkobi "Anayasası" gereği merkezi config ve helper'lara bağlanmıştır.
 * Değişiklik yaparken herkobi.php dosyasını referans alınız.
 */

declare(strict_types=1);

use App\Helpers\CurrencyHelper;

return [

    /*
    |--------------------------------------------------------------------------
    | PayTR Merchant Credentials
    |--------------------------------------------------------------------------
    */

    'merchant_id' => env('PAYTR_MERCHANT_ID'),
    'merchant_key' => env('PAYTR_MERCHANT_KEY'),
    'merchant_salt' => env('PAYTR_MERCHANT_SALT'),

    /*
    |--------------------------------------------------------------------------
    | PayTR API Endpoints
    |--------------------------------------------------------------------------
    */

    'iframe_url' => env('PAYTR_IFRAME_URL', 'https://www.paytr.com/odeme/guvenli/'),
    'api_url' => env('PAYTR_API_URL', 'https://www.paytr.com/odeme'),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */

    'callback_url' => env('PAYTR_CALLBACK_URL'),
    'success_url' => env('PAYTR_SUCCESS_URL'),
    'fail_url' => env('PAYTR_FAIL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */

    'test_mode' => env('PAYTR_TEST_MODE', true),
    'debug_on' => env('PAYTR_DEBUG', false),
    'timeout_limit' => env('PAYTR_TIMEOUT', 30),
    'currency' => CurrencyHelper::gatewayCode(),
    'lang' => env('PAYTR_LANG', 'tr'),
    'no_installment' => env('PAYTR_NO_INSTALLMENT', 1),
    'max_installment' => env('PAYTR_MAX_INSTALLMENT', 0),

    /*
    |--------------------------------------------------------------------------
    | Checkout Settings
    |--------------------------------------------------------------------------
    */

    /**
     * SEANS SÜRESİ:
     * Çakışmayı önlemek için doğrudan merkezi herkobi config'ine bağlandı.
     */
    'checkout_expiry_minutes' => config('herkobi.subscription.checkout_expiry_minutes', 30),

];
