<?php

/**
 * Plan Change Controller
 *
 * This controller handles plan upgrade and downgrade operations.
 * It manages plan change pages, proration calculations, and scheduling.
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

use App\Contracts\App\Account\PlanChangeServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Http\Controllers\Controller;
use App\Enums\CheckoutType;
use App\Http\Requests\App\Account\ChangePlanRequest;
use App\Models\Checkout;
use App\Models\PlanPrice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for plan change operations.
 *
 * Provides methods for displaying plan options, calculating proration,
 * and processing plan changes.
 */
class PlanChangeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlanChangeServiceInterface $planChangeService The plan change service
     */
    public function __construct(
        private readonly PlanChangeServiceInterface $planChangeService
    ) {}

    /**
     * Display the plan change page.
     *
     * @return Response
     */
    public function index(): Response
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return Inertia::render('app/Account/Plan/Change', [
                'error' => 'Aktif abonelik bulunamadı.',
            ]);
        }

        $subscription->load(['price.plan', 'nextPrice.plan']);

        $upgradeOptions = $this->planChangeService->getUpgradeOptions($tenant);
        $downgradeOptions = $this->planChangeService->getDowngradeOptions($tenant);

        return Inertia::render('app/Account/Plan/Change', [
            'currentSubscription' => [
                'id' => $subscription->id,
                'ends_at' => $subscription->ends_at?->toISOString(),
                'price' => [
                    'id' => $subscription->price->id,
                    'price' => $subscription->price->price,
                    'currency' => $subscription->price->currency,
                    'interval' => $subscription->price->interval->value,
                    'plan' => [
                        'id' => $subscription->price->plan->id,
                        'name' => $subscription->price->plan->name,
                    ],
                ],
                'next_price' => $subscription->nextPrice ? [
                    'id' => $subscription->nextPrice->id,
                    'price' => $subscription->nextPrice->price,
                    'plan' => [
                        'name' => $subscription->nextPrice->plan->name,
                    ],
                ] : null,
            ],
            'upgradeOptions' => $upgradeOptions,
            'downgradeOptions' => $downgradeOptions,
        ]);
    }

    /**
     * Display the plan change confirmation page.
     *
     * @param string $planPriceId The new plan price ID
     * @return Response
     */
    public function confirm(string $planPriceId): Response
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return Inertia::render('app/Account/Plan/Confirm', [
                'error' => 'Aktif abonelik bulunamadı.',
            ]);
        }

        $subscription->load('price.plan');
        $newPlanPrice = PlanPrice::with('plan')->findOrFail($planPriceId);

        $isUpgrade = $this->planChangeService->isUpgrade($subscription->price, $newPlanPrice);
        $prorationType = $this->planChangeService->resolveProrationType($subscription->price, $newPlanPrice);

        $proration = $isUpgrade
            ? $this->planChangeService->calculateUpgradeProration($subscription, $newPlanPrice)
            : $this->planChangeService->calculateDowngradeProration($subscription, $newPlanPrice);

        $isImmediate = $prorationType->isImmediate();

        return Inertia::render('app/Account/Plan/Confirm', [
            'currentPlan' => [
                'name' => $subscription->price->plan->name,
                'price' => $subscription->price->price,
            ],
            'newPlan' => [
                'id' => $newPlanPrice->id,
                'name' => $newPlanPrice->plan->name,
                'price' => $newPlanPrice->price,
                'currency' => $newPlanPrice->currency,
            ],
            'isUpgrade' => $isUpgrade,
            'prorationType' => $prorationType,
            'proration' => $proration,
            'effectiveAt' => $isImmediate ? null : $subscription->ends_at?->toISOString(),
        ]);
    }

    /**
     * Process the plan change request.
     *
     * @param ChangePlanRequest $request
     * @return RedirectResponse
     */
    public function change(ChangePlanRequest $request): RedirectResponse
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->route('app.account.plans.index')
                ->with('error', 'Aktif abonelik bulunamadı.');
        }

        $subscription->load('price');
        $newPlanPrice = PlanPrice::with('plan')->findOrFail($request->validated('plan_price_id'));

        $isUpgrade = $this->planChangeService->isUpgrade($subscription->price, $newPlanPrice);
        $prorationType = $this->planChangeService->resolveProrationType($subscription->price, $newPlanPrice);

        if ($prorationType->isImmediate()) {
            if ($isUpgrade) {
                return redirect()->route('app.account.checkout.index', [
                    'planPriceId' => $newPlanPrice->id,
                    'type' => CheckoutType::UPGRADE->value,
                ]);
            }

            $this->planChangeService->processImmediateDowngrade($subscription, $newPlanPrice);

            return redirect()->route('app.account.plans.index')
                ->with('success', 'Plan değişikliği başarıyla uygulandı.');
        }

        $this->planChangeService->schedulePlanChange($subscription, $newPlanPrice);

        return redirect()->route('app.account.plans.index')
            ->with('success', 'Plan değişikliği dönem sonunda uygulanacak şekilde planlandı.');
    }

    /**
     * Cancel a scheduled downgrade.
     *
     * @return RedirectResponse
     */
    public function cancelDowngrade(): RedirectResponse
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();
        $subscription = $tenant->subscription;

        if (!$subscription || !$subscription->next_plan_price_id) {
            return redirect()->route('app.account.plans.index')
                ->with('error', 'İptal edilecek plan değişikliği bulunamadı.');
        }

        $this->planChangeService->cancelScheduledDowngrade($subscription);

        return redirect()->route('app.account.plans.index')
            ->with('success', 'Planlanan plan değişikliği iptal edildi.');
    }
}
