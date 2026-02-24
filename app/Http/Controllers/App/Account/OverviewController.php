<?php

/**
 * Tenant Account Overview Controller
 *
 * This controller handles the account overview page displaying subscription
 * status, upcoming payments, and account summary for tenants.
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

use App\Services\App\Account\FeatureUsageService;
use App\Services\App\Account\PaymentService;
use App\Services\App\Account\SubscriptionService;
use App\Services\Shared\TenantContextService;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant account overview.
 *
 * Provides a summary view of subscription status, payments,
 * and account information.
 */
class OverviewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SubscriptionService $subscriptionService Service for subscription operations
     * @param PaymentService $paymentService Service for payment operations
     * @param FeatureUsageService $featureUsageService Service for feature usage operations
     */
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PaymentService $paymentService,
        private readonly FeatureUsageService $featureUsageService
    ) {}

    /**
     * Display the account overview page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $tenant = app(Tenant::class);

        $subscriptionDetails = $this->subscriptionService->getSubscriptionDetails($tenant);
        $hasSubscription = $subscriptionDetails['has_subscription'];

        $features = $hasSubscription
            ? $this->featureUsageService->getAllFeatures($tenant)
            : collect();

        $canUpgrade = $this->subscriptionService->canUpgrade($tenant);
        $statistics = $this->paymentService->getDashboardStatistics($tenant);
        $recentPayments = $this->paymentService->getRecentPayments($tenant);

        return Inertia::render('app/Account/Overview/Index', [
            'tenant' => $tenant,
            'hasActiveSubscription' => $tenant->hasActiveSubscription(),
            'subscription' => $hasSubscription
                ? $this->formatSubscriptionForDashboard($subscriptionDetails)
                : null,
            'features' => $features,
            'statistics' => $statistics,
            'recentPayments' => $recentPayments,
            'canUpgrade' => $canUpgrade,
        ]);
    }

    /**
     * Format subscription details for display.
     *
     * @param array $subscription
     * @return array
     */
    private function formatSubscriptionForDashboard(array $subscription): array
    {
        $sub = $subscription['subscription'];
        $price = $subscription['price'];

        return [
            'status' => [
                'label' => $sub['status']['label'],
                'badge' => $sub['status']['badge'],
            ],
            'plan' => [
                'name' => $subscription['plan']['name'] ?? '',
            ],
            'price' => [
                'amount' => $sub['custom_price'] ?? $price['amount'] ?? 0,
                'currency' => $sub['custom_currency'] ?? $price['currency'] ?? 'TRY',
                'interval_label' => $price['interval_label'] ?? '',
            ],
            'ends_at' => $sub['ends_at'],
        ];
    }
}
