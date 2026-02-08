<?php

/**
 * Plan Price Controller
 *
 * This controller handles plan price management operations including
 * creating, updating, and deleting price options for plans.
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

use App\Contracts\Panel\Plan\PlanPriceServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Plan\StorePlanPriceRequest;
use App\Http\Requests\Panel\Plan\UpdatePlanPriceRequest;
use App\Models\Plan;
use App\Models\PlanPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for plan price management.
 *
 * Provides CRUD operations for managing pricing options
 * associated with plans.
 */
class PlanPriceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlanPriceServiceInterface $planPriceService Service for plan price operations
     */
    public function __construct(
        private readonly PlanPriceServiceInterface $planPriceService
    ) {}

    /**
     * Store a newly created plan price.
     *
     * @param StorePlanPriceRequest $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function store(StorePlanPriceRequest $request, Plan $plan): RedirectResponse
    {
        $this->planPriceService->create(
            $plan,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Fiyat eklendi.');
    }

    /**
     * Update the specified plan price.
     *
     * @param UpdatePlanPriceRequest $request
     * @param Plan $plan
     * @param PlanPrice $price
     * @return RedirectResponse
     */
    public function update(UpdatePlanPriceRequest $request, Plan $plan, PlanPrice $price): RedirectResponse
    {
        if ((string) $price->plan_id !== (string) $plan->id) {
            return redirect()
                ->route('panel.plans.edit', $plan->id)
                ->with('error', 'Bu fiyat bu plana ait değil.');
        }

        $this->planPriceService->update(
            $price,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Fiyat güncellendi.');
    }

    /**
     * Remove the specified plan price.
     *
     * @param Request $request
     * @param Plan $plan
     * @param PlanPrice $price
     * @return RedirectResponse
     */
    public function destroy(Request $request, Plan $plan, PlanPrice $price): RedirectResponse
    {
        if ((string) $price->plan_id !== (string) $plan->id) {
            return redirect()
                ->route('panel.plans.edit', $plan->id)
                ->with('error', 'Bu fiyat bu plana ait değil.');
        }

        $this->planPriceService->delete(
            $price,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Fiyat silindi.');
    }
}
