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

use App\Contracts\Panel\Payment\PaymentServiceInterface;
use App\Contracts\Panel\Subscription\SubscriptionServiceInterface;
use App\Contracts\Panel\Tenant\TenantServiceInterface;
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
     * @param TenantServiceInterface $tenantService
     * @param PaymentServiceInterface $paymentService
     * @param SubscriptionServiceInterface $subscriptionService
     */
    public function __construct(
        private readonly TenantServiceInterface $tenantService,
        private readonly PaymentServiceInterface $paymentService,
        private readonly SubscriptionServiceInterface $subscriptionService
    ) {}

    /**
     * Display the panel dashboard.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Tenant istatistikleri
        $totalTenants = $this->tenantService->getAll()->count();
        $activeTenants = $this->tenantService->getActive()->count();

        // Payment istatistikleri
        $paymentStats = $this->paymentService->getStatistics();

        // Subscription istatistikleri
        $subscriptionStats = $this->subscriptionService->getStatistics();

        // Son ödemeler (5 adet)
        $recentPayments = $this->paymentService->getPaginated([], 5);

        // Son hesaplar (5 adet)
        $recentTenants = $this->tenantService->getPaginated(['sort_field' => 'created_at', 'sort_direction' => 'desc'], 5);

        // Son aktiviteler (15 adet) - Activity model varsa
        $recentActivities = \App\Models\Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Plan dağılımı - DB seviyesinde aggregation
        $planDistribution = \App\Models\Subscription::query()
            ->join('plan_prices', 'subscriptions.plan_price_id', '=', 'plan_prices.id')
            ->join('plans', 'plan_prices.plan_id', '=', 'plans.id')
            ->selectRaw('plans.name, COUNT(*) as subscriber_count, SUM(plan_prices.price) as total_revenue')
            ->groupBy('plans.name')
            ->get();

        // Uyarılar
        $expiringSubscriptions = \App\Models\Subscription::whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->with('tenant')
            ->limit(10)
            ->get();

        $failedPayments = \App\Models\Payment::withoutTenantScope()
            ->where('status', \App\Enums\PaymentStatus::FAILED)
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

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
