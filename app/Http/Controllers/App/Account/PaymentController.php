<?php

/**
 * Tenant Payment Controller
 *
 * This controller handles payment history viewing for tenants
 * including listing and viewing individual payment details.
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

use App\Contracts\App\Account\PaymentServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\PaymentFilterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant payment history.
 *
 * Provides functionality for viewing payment history and
 * individual payment details.
 */
class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PaymentServiceInterface $paymentService Service for payment operations
     */
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    /**
     * Display a listing of payments.
     *
     * @param PaymentFilterRequest $request
     * @return Response
     */
    public function index(PaymentFilterRequest $request): Response
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();

        $payments = $this->paymentService->getPaginated(
            $tenant,
            $request->validated(),
            $request->integer('per_page', 15)
        );

        return Inertia::render('app/Account/Payments/Index', [
            'payments' => $payments,
            'statistics' => $this->paymentService->getStatistics($tenant),
            'filters' => $request->validated(),
        ]);
    }

    /**
     * Display the specified payment.
     *
     * @param Request $request
     * @param string $paymentId
     * @return Response|RedirectResponse
     */
    public function show(Request $request, string $paymentId): Response|RedirectResponse
    {
        $payment = $this->paymentService->findById(app(TenantContextServiceInterface::class)->currentTenant(), $paymentId);

        if (!$payment) {
            return redirect()
                ->route('app.payments.index')
                ->with('error', 'Ödeme kaydı bulunamadı.');
        }

        return Inertia::render('app/Account/Payments/Show', [
            'payment' => $payment,
        ]);
    }
}
