<?php

/**
 * Panel Tenant Feature Controller
 *
 * This controller handles tenant feature override management operations
 * for the panel, including listing, syncing, and removing overrides.
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

use App\Services\Panel\Tenant\TenantFeatureService;
use App\Services\Panel\Tenant\TenantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Tenant\SyncTenantFeaturesRequest;
use App\Models\Feature;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel tenant feature override management.
 *
 * Provides methods for feature override CRUD operations with comprehensive
 * validation and audit logging.
 */
class TenantFeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantFeatureService $featureService Service for feature operations
     * @param TenantService $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly TenantFeatureService $featureService,
        private readonly TenantService $tenantService
    ) {}

    /**
     * Display feature overrides for a tenant.
     *
     * @param Tenant $tenant
     * @return View
     */
    public function index(Tenant $tenant): Response
    {
        $effectiveFeatures = $this->featureService->getEffectiveFeatures($tenant);
        $planFeatures = $this->featureService->getPlanFeatures($tenant);
        $overrides = $this->featureService->getOverrides($tenant);
        $allFeatures = Feature::orderBy('name')->get(['id', 'name', 'slug', 'type']);
        $statistics = $this->tenantService->getStatistics($tenant);

        return Inertia::render('panel/Tenants/Features', [
            'tenant' => $tenant,
            'effectiveFeatures' => $effectiveFeatures,
            'planFeatures' => $planFeatures,
            'overrides' => $overrides,
            'allFeatures' => $allFeatures,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Sync feature overrides for a tenant.
     *
     * @param SyncTenantFeaturesRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function sync(SyncTenantFeaturesRequest $request, Tenant $tenant): RedirectResponse
    {
        $overrides = [];
        foreach ($request->validated('overrides', []) as $override) {
            $overrides[$override['feature_id']] = $override['value'];
        }

        $this->featureService->syncOverrides(
            $tenant,
            $overrides,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.features.index', $tenant)
            ->with('success', 'Özellik değerleri başarıyla güncellendi.');
    }

    /**
     * Remove a specific feature override for a tenant.
     *
     * @param Request $request
     * @param Tenant $tenant
     * @param string $featureId
     * @return RedirectResponse
     */
    public function destroy(Request $request, Tenant $tenant, string $featureId): RedirectResponse
    {
        $feature = Feature::findOrFail($featureId);

        $this->featureService->removeOverride(
            $tenant,
            $featureId,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.features.index', $tenant)
            ->with('success', "'{$feature->name}' özelliği için özel değer kaldırıldı.");
    }

    /**
     * Clear all feature overrides for a tenant.
     *
     * @param Request $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function clear(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->featureService->clearAllOverrides(
            $tenant,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.features.index', $tenant)
            ->with('success', 'Tüm özel özellik değerleri kaldırıldı.');
    }
}
