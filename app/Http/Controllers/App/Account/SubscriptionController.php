<?php

/**
 * Tenant Subscription Controller
 *
 * This controller handles subscription viewing and management for tenants
 * including viewing subscription details and available features.
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

use App\Contracts\App\Account\FeatureUsageServiceInterface;
use App\Contracts\App\Account\SubscriptionServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant subscription management.
 *
 * Provides functionality for viewing subscription details,
 * features, and available plans.
 */
class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SubscriptionServiceInterface $subscriptionService Service for subscription operations
     * @param FeatureUsageServiceInterface $featureUsageService Service for feature usage operations
     */
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptionService,
        private readonly FeatureUsageServiceInterface $featureUsageService
    ) {}

    /**
     * Display the subscription details page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $tenant = app(TenantContextServiceInterface::class)->currentTenant();

        return Inertia::render('app/Account/Subscription/Index', [
            'subscription' => $this->subscriptionService->getSubscriptionDetails($tenant),
            'features' => $this->featureUsageService->getAllFeatures($tenant),
            'availablePlans' => $this->subscriptionService->getAvailablePlans(),
            'canUpgrade' => $this->subscriptionService->canUpgrade($tenant),
            'canDowngrade' => $this->subscriptionService->canDowngrade($tenant),
        ]);
    }
}
