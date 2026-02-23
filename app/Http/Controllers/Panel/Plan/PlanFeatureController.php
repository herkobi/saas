<?php

/**
 * Plan Feature Controller
 *
 * Handles the management of features associated with plans.
 * Manages the many-to-many relationship between plans and features,
 * including viewing and syncing feature assignments.
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

use App\Services\Panel\Plan\FeatureService;
use App\Services\Panel\Plan\PlanService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Plan\SyncPlanFeaturesRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Plan Feature Controller
 *
 * Manages the association between plans and their features,
 * handling feature assignment and synchronization operations.
 */
class PlanFeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlanService $planService Service for plan operations
     * @param FeatureService $featureService Service for feature operations
     */
    public function __construct(
        private readonly PlanService $planService
    ) {}

    /**
     * Sync the plan's features.
     *
     * Updates the plan-feature relationships, including the specific
     * values/limits for each feature assigned to the plan.
     *
     * @param SyncPlanFeaturesRequest $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function sync(SyncPlanFeaturesRequest $request, Plan $plan): RedirectResponse
    {
        $this->planService->syncFeatures(
            $plan,
            $request->validated('features', []),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Plan özellikleri güncellendi.');
    }
}
