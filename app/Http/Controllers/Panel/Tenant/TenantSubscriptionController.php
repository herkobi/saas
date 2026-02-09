<?php

/**
 * Panel Tenant Subscription Controller
 *
 * This controller handles tenant subscription management operations for the panel,
 * including creating, cancelling, renewing, and changing subscription plans.
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

use App\Contracts\Panel\Tenant\TenantSubscriptionServiceInterface;
use App\Contracts\Panel\Tenant\TenantServiceInterface;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Tenant\CancelSubscriptionRequest;
use App\Http\Requests\Panel\Tenant\ChangeSubscriptionPlanRequest;
use App\Http\Requests\Panel\Tenant\CreateSubscriptionRequest;
use App\Http\Requests\Panel\Tenant\ExtendGracePeriodRequest;
use App\Http\Requests\Panel\Tenant\ExtendTrialRequest;
use App\Http\Requests\Panel\Tenant\RenewSubscriptionRequest;
use App\Http\Requests\Panel\Tenant\UpdateSubscriptionStatusRequest;
use App\Models\PlanPrice;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel tenant subscription management.
 *
 * Provides methods for subscription CRUD operations with comprehensive
 * validation and audit logging.
 */
class TenantSubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantSubscriptionServiceInterface $subscriptionService Service for subscription operations
     * @param TenantServiceInterface $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly TenantSubscriptionServiceInterface $subscriptionService,
        private readonly TenantServiceInterface $tenantService
    ) {}

    /**
     * Display the current subscription for a tenant.
     *
     * @param Tenant $tenant
     * @return Response
     */
    public function show(Tenant $tenant): Response
    {
        $subscription = $this->subscriptionService->getCurrentSubscription($tenant);
        $history = $this->subscriptionService->getSubscriptionHistory($tenant);
        $statistics = $this->tenantService->getStatistics($tenant);

        return Inertia::render('panel/Tenants/Subscription', [
            'tenant' => $tenant,
            'subscription' => $subscription,
            'history' => $history,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Create a new subscription for a tenant.
     *
     * @param CreateSubscriptionRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function store(CreateSubscriptionRequest $request, Tenant $tenant): RedirectResponse
    {
        $planPrice = PlanPrice::findOrFail($request->validated('plan_price_id'));

        $options = [];
        if ($request->validated('trial_days') !== null) {
            $options['trial_days'] = (int) $request->validated('trial_days');
        }

        $this->subscriptionService->create(
            $tenant,
            $planPrice,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            $options
        );

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', 'Abonelik başarıyla oluşturuldu.');
    }

    /**
     * Change the subscription plan for a tenant.
     *
     * @param ChangeSubscriptionPlanRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function changePlan(ChangeSubscriptionPlanRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aktif bir aboneliği bulunmuyor.');
        }

        $newPlanPrice = PlanPrice::findOrFail($request->validated('plan_price_id'));
        $immediate = (bool) $request->validated('immediate', false);

        $this->subscriptionService->changePlan(
            $subscription,
            $newPlanPrice,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            $immediate
        );

        $message = $immediate
            ? 'Plan değişikliği hemen uygulandı.'
            : 'Plan değişikliği dönem sonunda uygulanacak.';

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', $message);
    }

    /**
     * Cancel a tenant's subscription.
     *
     * @param CancelSubscriptionRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function cancel(CancelSubscriptionRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aktif bir aboneliği bulunmuyor.');
        }

        $immediate = (bool) $request->validated('immediate', false);

        $this->subscriptionService->cancel(
            $subscription,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            $immediate
        );

        $message = $immediate
            ? 'Abonelik hemen iptal edildi.'
            : 'Abonelik dönem sonunda iptal edilecek.';

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', $message);
    }

    /**
     * Renew a tenant's subscription.
     *
     * @param RenewSubscriptionRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function renew(RenewSubscriptionRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aboneliği bulunmuyor.');
        }

        $this->subscriptionService->renew(
            $subscription,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', 'Abonelik başarıyla yenilendi.');
    }

    /**
     * Extend the trial period for a tenant's subscription.
     *
     * @param ExtendTrialRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function extendTrial(ExtendTrialRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aboneliği bulunmuyor.');
        }

        $this->subscriptionService->extendTrial(
            $subscription,
            (int) $request->validated('days'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', 'Deneme süresi başarıyla uzatıldı.');
    }

    /**
     * Extend the grace period for a tenant's subscription.
     *
     * @param ExtendGracePeriodRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function extendGracePeriod(ExtendGracePeriodRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aboneliği bulunmuyor.');
        }

        $this->subscriptionService->extendGracePeriod(
            $subscription,
            (int) $request->validated('days'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', 'Ek süre başarıyla uzatıldı.');
    }

    /**
     * Manually update the status of a tenant's subscription.
     *
     * This is an admin override for exceptional cases.
     *
     * @param UpdateSubscriptionStatusRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function updateStatus(UpdateSubscriptionStatusRequest $request, Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()
                ->route('panel.tenants.subscription.show', $tenant)
                ->with('error', 'Tenant\'ın aboneliği bulunmuyor.');
        }

        $this->subscriptionService->updateStatus(
            $subscription,
            SubscriptionStatus::from($request->validated('status')),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            $request->validated('reason')
        );

        return redirect()
            ->route('panel.tenants.subscription.show', $tenant)
            ->with('success', 'Abonelik durumu başarıyla güncellendi.');
    }
}
