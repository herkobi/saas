<?php

/**
 * Tenant Dashboard Controller
 *
 * This controller handles the main dashboard view for tenant users,
 * displaying key statistics and overview information.
 *
 * @package    App\Http\Controllers\App
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Services\App\Account\FeatureUsageService;
use App\Services\App\Account\PaymentService;
use App\Services\App\Account\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant dashboard.
 *
 * Provides the main overview page for tenant users with
 * key metrics and statistics.
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SubscriptionService $subscriptionService
     * @param PaymentService $paymentService
     * @param FeatureUsageService $featureUsageService
     */
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PaymentService $paymentService,
        private readonly FeatureUsageService $featureUsageService,
    ) {}

    /**
     * Display the tenant dashboard.
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

        return Inertia::render('app/Dashboard', [
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
     * Format subscription details for dashboard display.
     *
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    private function formatSubscriptionForDashboard(array $details): array
    {
        $subscription = $details['subscription'];
        $price = $details['price'];

        return [
            'status' => [
                'label' => $subscription['status']['label'],
                'badge' => $subscription['status']['badge'],
            ],
            'plan' => [
                'name' => $details['plan']['name'] ?? '',
            ],
            'price' => [
                'amount' => $subscription['custom_price'] ?? $price['amount'] ?? 0,
                'currency' => $subscription['custom_currency'] ?? $price['currency'] ?? 'TRY',
                'interval_label' => $price['interval_label'] ?? '',
            ],
            'ends_at' => $subscription['ends_at'],
        ];
    }
}
