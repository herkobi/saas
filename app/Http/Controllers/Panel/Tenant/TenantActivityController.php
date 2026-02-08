<?php

/**
 * Panel Tenant Activity Controller
 *
 * This controller handles tenant activity history retrieval operations
 * for the panel, providing paginated access to tenant activities.
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
 * Controller for panel tenant activity viewing.
 *
 * Provides methods for listing tenant activities with pagination
 * for audit tracking and monitoring purposes.
 */
class TenantActivityController extends Controller
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
     * Display a paginated listing of activities for a tenant.
     *
     * @param Request $request
     * @param Tenant $tenant
     * @return View
     */
    public function index(Request $request, Tenant $tenant): Response
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $activities = $this->tenantService->getActivities($tenant, $perPage);
        $statistics = $this->tenantService->getStatistics($tenant);

        return Inertia::render('panel/tenants.activities', [
            'tenant' => $tenant,
            'activities' => $activities,
            'statistics' => $statistics,
        ]);
    }
}
