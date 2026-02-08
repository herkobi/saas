<?php

/**
 * Plan Controller
 *
 * This controller handles plan management operations including
 * CRUD operations, archiving, publishing and restoring plans.
 *
 * @package    App\Http\Controllers\Panel\Plan
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Plan;

use App\Contracts\Panel\Plan\FeatureServiceInterface;
use App\Contracts\Panel\Plan\PlanServiceInterface;
use App\Contracts\Panel\Plan\PlanPriceServiceInterface;
use App\Contracts\Panel\Tenant\TenantServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Plan\StorePlanRequest;
use App\Http\Requests\Panel\Plan\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for plan management.
 *
 * Provides CRUD operations and plan lifecycle management
 * including archiving, restoring and publishing.
 */
class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlanServiceInterface $planService Service for plan operations
     * @param PlanPriceServiceInterface $planPriceService Service for plan price operations
     * @param FeatureServiceInterface $featureService Service for feature operations
     * @param TenantServiceInterface $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly PlanServiceInterface $planService,
        private readonly PlanPriceServiceInterface $planPriceService,
        private readonly FeatureServiceInterface $featureService,
        private readonly TenantServiceInterface $tenantService
    ) {}

    /**
     * Display a listing of plans.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['archived']);

        return Inertia::render('panel/Plans/Index', [
            'plans' => $this->planService->getPaginated(
                $filters,
                (int) $request->get('per_page', 15)
            ),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new plan.
     *
     * @return View
     */
    public function create(): Response
    {
        return Inertia::render('panel/Plans/Create', [
            'tenants' => $this->tenantService->getActive(),
        ]);
    }

    /**
     * Store a newly created plan.
     *
     * @param StorePlanRequest $request
     * @return RedirectResponse
     */
    public function store(StorePlanRequest $request): RedirectResponse
    {
        $plan = $this->planService->create(
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.plans.edit', $plan->id)
            ->with('success', 'Plan oluşturuldu.');
    }

    /**
     * Show the form for editing the specified plan.
     *
     * @param Plan $plan
     * @return View
     */
    public function edit(Plan $plan): Response
    {
        $plan = $this->planService->findOrFailById($plan->id);

        return Inertia::render('panel/Plans/Edit', [
            'plan' => $plan,
            'features' => $this->featureService->getActive(),
            'prices' => $this->planPriceService->getByPlan($plan),
            'enabledFeatures' => $this->planService->getEnabledFeaturesForEdit($plan),
            'tenants' => $this->tenantService->getActive(),
            'intervalCases' => \App\Enums\PlanInterval::cases(),
            'planTenantsCount' => $this->planService->getTenantsUsingPlan($plan)->count(),
            'tenantList' => $this->planService->getTenantListForDisplay($plan),
        ]);
    }

    /**
     * Update the specified plan.
     *
     * @param UpdatePlanRequest $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->planService->update(
            $plan,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan güncellendi.');
    }

    /**
     * Archive the specified plan.
     *
     * Only plans without active subscriptions can be archived.
     *
     * @param Request $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function archive(Request $request, Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->count() > 0) {
            return back()->with('error', 'Bu plan aktif aboneliklere sahip olduğu için arşivlenemez.');
        }

        $this->planService->archive(
            $plan,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan arşivlendi.');
    }

    /**
     * Restore the specified plan.
     *
     * @param Request $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function restore(Request $request, Plan $plan): RedirectResponse
    {
        $this->planService->restore(
            $plan,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan geri yüklendi.');
    }

    /**
     * Publish the specified plan.
     *
     * Only plans with prices and features can be published.
     *
     * @param Request $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function publish(Request $request, Plan $plan): RedirectResponse
    {
        // Route model binding ile gelen $plan ilişkileri yüklenmemiş olabilir
        $plan->loadMissing(['prices', 'features']);

        if ($plan->prices->isEmpty()) {
            return back()->with('error', 'Plan yayınlanamadı. En az 1 fiyat eklenmelidir.');
        }

        if ($plan->features->isEmpty()) {
            return back()->with('error', 'Plan yayınlanamadı. En az 1 özellik atanmalıdır.');
        }

        $this->planService->publish(
            $plan,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan yayınlandı.');
    }

    /**
     * Unpublish the specified plan.
     *
     * @param Request $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function unpublish(Request $request, Plan $plan): RedirectResponse
    {
        $this->planService->unpublish(
            $plan,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan yayından kaldırıldı.');
    }
}
