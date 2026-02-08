<?php

/**
 * Ensure Active Subscription Middleware
 *
 * This middleware verifies that the current tenant has an active (valid) subscription.
 * It prevents access to protected resources if the subscription has expired.
 *
 * @package    App\Http\Middleware
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Shared\TenantContextServiceInterface;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for verifying tenant subscription status.
 *
 * Checks if the tenant has an active subscription and blocks access
 * to protected resources if the subscription is expired.
 */
class EnsureActiveSubscription
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContextService->currentTenant();

        if (!$tenant instanceof Tenant) {
            return redirect()->route('register');
        }

        if (!$tenant->hasActiveSubscription()) {
            return redirect()->route('login')->with('error', 'Aboneliğiniz aktif değil veya süresi dolmuş. Lütfen aboneliğinizi yenileyin.');
        }

        return $next($request);
    }
}
