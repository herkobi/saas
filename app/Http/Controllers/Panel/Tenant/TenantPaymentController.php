<?php

/**
 * Panel Tenant Payment Controller
 *
 * This controller handles tenant payment history retrieval operations
 * for the panel, providing paginated access to tenant payments.
 *
 * @package    App\Http\Controllers\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Tenant;

use App\Contracts\Panel\Tenant\TenantServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel tenant payment viewing.
 *
 * Provides methods for listing tenant payments with pagination
 * for financial tracking and reporting purposes.
 */
class TenantPaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantServiceInterface $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly TenantServiceInterface $tenantService
    ) {}

    /**
     * Display a paginated listing of payments for a tenant.
     *
     * @param Request $request
     * @param Tenant $tenant
     * @return View
     */
    public function index(Request $request, Tenant $tenant): Response
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $payments = $this->tenantService->getPayments($tenant, $perPage);
        $statistics = $this->tenantService->getStatistics($tenant);

        return Inertia::render('panel/tenants.payments', [
            'tenant' => $tenant,
            'payments' => $payments,
            'statistics' => $statistics,
        ]);
    }
}
