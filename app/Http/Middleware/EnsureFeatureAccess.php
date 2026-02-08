<?php

/**
 * Ensure Feature Access Middleware
 *
 * This middleware checks whether the current tenant's plan includes
 * the specified feature (boolean, limit, or metered).
 *
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 *
 * @version    1.0.0
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\App\Account\FeatureUsageServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Models\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce plan-based feature access.
 *
 * Supports all three feature types:
 * - FEATURE (boolean): blocks all access if plan doesn't include the feature
 * - LIMIT (numerical): blocks only write requests (POST/PUT/DELETE) when limit reached
 * - METERED (usage counter): blocks only write requests when quota exceeded
 *
 * For LIMIT/METERED types, GET requests always pass through so users can
 * still view their data even when at the limit.
 */
class EnsureFeatureAccess
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService,
        private readonly FeatureUsageServiceInterface $featureUsageService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  string  $featureSlug  The feature slug to check
     */
    public function handle(Request $request, Closure $next, string $featureSlug): Response
    {
        $tenant = $this->tenantContextService->currentTenant();

        if (! $tenant) {
            return $this->deny($request, 'Tenant bulunamadı.');
        }

        $feature = Feature::where('slug', $featureSlug)->first();

        if (! $feature) {
            return $next($request);
        }

        if ($feature->isFeature()) {
            if (! $this->featureUsageService->checkFeatureAccess($tenant, $featureSlug)) {
                return $this->deny($request, 'Bu özellik planınızda bulunmuyor.');
            }
        }

        if (($feature->isLimit() || $feature->isMetered()) && ! $request->isMethod('GET')) {
            $check = $this->featureUsageService->checkFeatureLimit($tenant, $featureSlug);
            if (! $check['allowed']) {
                return $this->deny($request, $check['reason'] ?? 'Özellik limitine ulaştınız.');
            }
        }

        return $next($request);
    }

    /**
     * Deny access with appropriate response.
     */
    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back()->with('toast', [
            'type' => 'error',
            'message' => $message,
        ]);
    }
}
