<?php

/**
 * Feature Usage Controller
 *
 * This controller handles feature usage API endpoints for tenants
 * including limits, metered usage, and boolean features.
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
use App\Services\Shared\TenantContextService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for tenant feature usage API.
 *
 * Provides JSON endpoints for checking feature limits,
 * usage statistics, and availability.
 */
class FeatureUsageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param FeatureUsageService $featureUsageService Service for feature usage operations
     */
    public function __construct(
        private readonly FeatureUsageService $featureUsageService
    ) {}

    /**
     * Get all features and their usage for the tenant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app(TenantContextService::class)->currentTenant();

        return response()->json([
            'features' => $this->featureUsageService->getAllFeatures($tenant),
            'limits' => $this->featureUsageService->getLimitFeatures($tenant),
            'metered' => $this->featureUsageService->getMeteredFeatures($tenant),
            'boolean' => $this->featureUsageService->getBooleanFeatures($tenant),
        ]);
    }

    /**
     * Get usage details for a specific feature.
     *
     * @param Request $request
     * @param string $featureSlug The feature slug
     * @return JsonResponse
     */
    public function show(Request $request, string $featureSlug): JsonResponse
    {
        $usage = $this->featureUsageService->getFeatureUsage(app(TenantContextService::class)->currentTenant(), $featureSlug);

        return response()->json($usage);
    }

    /**
     * Check if a feature limit allows usage.
     *
     * @param Request $request
     * @param string $featureSlug The feature slug
     * @return JsonResponse
     */
    public function check(Request $request, string $featureSlug): JsonResponse
    {
        $result = $this->featureUsageService->checkFeatureLimit(app(TenantContextService::class)->currentTenant(), $featureSlug);

        return response()->json($result);
    }
}
