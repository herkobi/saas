<?php

/**
 * Panel Tenant Controller
 *
 * This controller handles tenant management operations for the panel,
 * including listing, viewing details, and updating tenant information.
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

use App\Services\Panel\Tenant\TenantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Tenant\TenantFilterRequest;
use App\Http\Requests\Panel\Tenant\UpdateTenantRequest;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel tenant management.
 *
 * Provides methods for listing, viewing, and updating tenants
 * with comprehensive filtering and statistics.
 */
class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantService $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    /**
     * Display a listing of tenants.
     *
     * @param TenantFilterRequest $request
     * @return View
     */
    public function index(TenantFilterRequest $request): Response
    {
        $filters = [
            'search' => $request->validated('search'),
            'status' => $request->validated('status'),
            'subscription_status' => $request->validated('subscription_status'),
            'plan_id' => $request->validated('plan_id'),
            'created_from' => $request->validated('created_from'),
            'created_to' => $request->validated('created_to'),
            'sort_field' => $request->validated('sort_field') ?? 'created_at',
            'sort_direction' => $request->validated('sort_direction') ?? 'desc',
        ];

        $perPage = (int) ($request->validated('per_page') ?? 15);

        $tenants = $this->tenantService->getPaginated($filters, $perPage);

        $plans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('panel/Tenants/Index', [
            'tenants' => $tenants,
            'plans' => $plans,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified tenant.
     *
     * @param Request $request
     * @param Tenant $tenant
     * @return View
     */
    public function show(Request $request, Tenant $tenant): Response
    {
        $tenant = $this->tenantService->findById($tenant->id);
        $statistics = $this->tenantService->getStatistics($tenant);

        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);
        $activities = $this->tenantService->getActivities($tenant, $perPage);

        return Inertia::render('panel/Tenants/Show', [
            'tenant' => $tenant,
            'statistics' => $statistics,
            'activities' => $activities,
        ]);
    }

    /**
     * Update the specified tenant.
     *
     * @param UpdateTenantRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->tenantService->update(
            $tenant,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.show', $tenant)
            ->with('success', 'Tenant bilgileri başarıyla güncellendi.');
    }
}
