<?php

/**
 * Panel Dashboard Controller
 *
 * This controller handles the main dashboard view for panel users,
 * displaying key statistics and overview information.
 *
 * @package    App\Http\Controllers\Panel
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Services\Panel\Payment\PaymentService;
use App\Services\Panel\Subscription\SubscriptionService;
use App\Services\Panel\Tenant\TenantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel dashboard.
 *
 * Provides the main overview page for panel users with
 * key metrics and statistics.
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantService $tenantService
     * @param PaymentService $paymentService
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly PaymentService $paymentService,
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * Display the panel dashboard.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $totalTenants = $this->tenantService->getAll()->count();
        $activeTenants = $this->tenantService->getActive()->count();
        $paymentStats = $this->paymentService->getStatistics();
        $subscriptionStats = $this->subscriptionService->getStatistics();
        $recentPayments = $this->paymentService->getPaginated([], 5);
        $recentTenants = $this->tenantService->getPaginated(['sort_field' => 'created_at', 'sort_direction' => 'desc'], 5);
        $recentActivities = $this->tenantService->getRecentActivities();
        $planDistribution = $this->subscriptionService->getPlanDistribution();
        $expiringSubscriptions = $this->subscriptionService->getExpiringSubscriptions();
        $failedPayments = $this->paymentService->getFailedPayments();

        return Inertia::render('panel/Dashboard', [
            'totalTenants' => $totalTenants,
            'activeTenants' => $activeTenants,
            'paymentStats' => $paymentStats,
            'subscriptionStats' => $subscriptionStats,
            'recentPayments' => $recentPayments,
            'recentTenants' => $recentTenants,
            'recentActivities' => $recentActivities,
            'planDistribution' => $planDistribution,
            'expiringSubscriptions' => $expiringSubscriptions,
            'failedPayments' => $failedPayments,
        ]);
    }
}
