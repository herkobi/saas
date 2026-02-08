<?php

/**
 * Tenant Billing Controller
 *
 * This controller handles billing information management for tenants
 * including viewing and updating billing details.
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

use App\Contracts\App\Account\BillingServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\UpdateBillingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant billing information.
 *
 * Provides functionality for viewing and updating billing details
 * for the tenant account.
 */
class BillingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param BillingServiceInterface $billingService Service for billing operations
     */
    public function __construct(
        private readonly BillingServiceInterface $billingService
    ) {}

    /**
     * Display the billing information page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();

        return Inertia::render('app/Account/Billing/Index', [
            'account' => $this->billingService->getAccount($tenant),
            'billingInfo' => $this->billingService->getBillingInfo($tenant),
        ]);
    }

    /**
     * Update the billing information.
     *
     * @param UpdateBillingRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateBillingRequest $request): RedirectResponse
    {
        $this->billingService->updateBillingInfo(
            app(TenantContextServiceInterface::class)->currentTenant(),
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Fatura bilgileri başarıyla güncellendi.');
    }
}
