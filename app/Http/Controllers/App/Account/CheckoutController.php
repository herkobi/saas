<?php

/**
 * Checkout Controller
 *
 * This controller handles checkout operations for tenant subscription purchases.
 * It manages checkout initiation, payment processing, and result pages.
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
use App\Services\Shared\TenantContextService;
use App\Enums\CheckoutType;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\InitiateCheckoutRequest;
use App\Models\Checkout;
use App\Models\PlanPrice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant checkout operations.
 *
 * Provides methods for displaying checkout pages, initiating payments,
 * and handling payment results.
 */
class CheckoutController extends Controller
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
     * Display the checkout page.
     *
     * @param string $planPriceId The plan price ID
     * @param string $type The checkout type
     * @return Response
     */
    public function index(string $planPriceId, string $type = 'new'): Response
    {
        $tenant = app(TenantContextService::class)->currentTenant();
        $planPrice = PlanPrice::with('plan')->findOrFail($planPriceId);

        $amounts = $this->checkoutService->calculateAmount($tenant, $planPrice, CheckoutType::from($type));

        return Inertia::render('app/Account/Checkout/Index', [
            'planPrice' => [
                'id' => $planPrice->id,
                'price' => $planPrice->price,
                'currency' => $planPrice->currency,
                'interval' => $planPrice->interval->value,
                'interval_count' => $planPrice->interval_count,
                'plan' => [
                    'id' => $planPrice->plan->id,
                    'name' => $planPrice->plan->name,
                    'description' => $planPrice->plan->description,
                ],
            ],
            'type' => $type,
            'amounts' => $amounts,
            'billingInfo' => $tenant->account ?? [],
        ]);
    }

    /**
     * Initiate a checkout session.
     *
     * @param InitiateCheckoutRequest $request
     * @return RedirectResponse
     */
    public function initiate(InitiateCheckoutRequest $request): RedirectResponse
    {
        $tenant = app(TenantContextService::class)->currentTenant();
        $planPrice = PlanPrice::findOrFail($request->validated('plan_price_id'));

        $checkout = $this->checkoutService->initiate(
            $tenant,
            $planPrice,
            CheckoutType::from($request->validated('type')),
            $request->validated('billing_info', [])
        );

        return redirect()->route('app.account.checkout.processing', ['checkoutId' => $checkout->id]);
    }

    /**
     * Display the payment processing page with PayTR iframe.
     *
     * @param string $checkoutId The checkout ID
     * @return Response|RedirectResponse
     */
    public function processing(string $checkoutId): Response|RedirectResponse
    {
        if (!$this->isPaymentGatewayConfigured()) {
            return redirect()->route('app.account.checkout.failed')
                ->with('error', 'Ödeme altyapısı henüz yapılandırılmamış. Lütfen yönetici ile iletişime geçin.');
        }

        $checkout = Checkout::with('planPrice.plan')->findOrFail($checkoutId);

        if ($checkout->isCompleted()) {
            return redirect()->route('app.account.checkout.success');
        }

        if ($checkout->isExpired()) {
            return redirect()->route('app.account.checkout.failed')
                ->with('error', 'Ödeme süresi doldu. Lütfen tekrar deneyin.');
        }

        $user = request()->user();
        $tenant = app(TenantContextService::class)->currentTenant();

        $result = $this->checkoutService->generatePaymentToken($checkout, [
            'user_ip' => request()->ip(),
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $tenant->account['phone'] ?? '',
            'address' => $this->formatAddress($tenant->account ?? []),
        ]);

        if (!$result['success']) {
            return redirect()->route('app.account.checkout.failed')
                ->with('error', $result['error']);
        }

        return Inertia::render('app/Account/Checkout/Processing', [
            'checkout' => [
                'id' => $checkout->id,
                'merchant_oid' => $checkout->merchant_oid,
                'final_amount' => $checkout->final_amount,
                'currency' => $checkout->currency,
            ],
            'planPrice' => [
                'plan' => [
                    'name' => $checkout->planPrice->plan->name,
                ],
            ],
            'iframeUrl' => $result['iframe_url'],
        ]);
    }

    /**
     * Display the success page.
     *
     * @return Response
     */
    public function success(): Response
    {
        return Inertia::render('app/Account/Checkout/Success');
    }

    /**
     * Display the failed page.
     *
     * @return Response
     */
    public function failed(): Response
    {
        return Inertia::render('app/Account/Checkout/Failed', [
            'error' => session('error'),
        ]);
    }

    /**
     * Format the billing address.
     *
     * @param array<string, mixed> $account The account data
     * @return string
     */
    protected function formatAddress(array $account): string
    {
        $parts = array_filter([
            $account['address'] ?? '',
            $account['city'] ?? '',
            $account['country'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if payment gateway credentials are configured.
     */
    protected function isPaymentGatewayConfigured(): bool
    {
        return !empty(config('paytr.merchant_id'))
            && !empty(config('paytr.merchant_key'))
            && !empty(config('paytr.merchant_salt'));
    }
}
