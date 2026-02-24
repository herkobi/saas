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

        return Inertia::render('app/Dashboard', [
            'tenant' => $tenant
        ]);
    }
}
