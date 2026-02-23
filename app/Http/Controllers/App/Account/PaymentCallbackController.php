<?php

/**
 * Payment Callback Controller
 *
 * This controller handles payment gateway callbacks (webhooks).
 * It processes server-to-server notifications from PayTR.
 *
 * @package    App\Http\Controllers\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App\Account;

use App\Services\App\Account\CheckoutService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling payment gateway webhooks.
 *
 * Processes PayTR callback notifications and updates checkout/payment status.
 */
class PaymentCallbackController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CheckoutService $checkoutService The checkout service
     */
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Handle the PayTR callback.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            $result = $this->checkoutService->processCallback($request->all());

            if (!$result['success']) {
                Log::error('PayTR callback processing failed', [
                    'error' => $result['error'],
                    'merchant_oid' => $request->input('merchant_oid'),
                ]);

                return response('FAIL', 200);
            }

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('PayTR callback exception', [
                'message' => $e->getMessage(),
                'merchant_oid' => $request->input('merchant_oid'),
            ]);

            return response('FAIL', 200);
        }
    }
}
