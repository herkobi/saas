<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Shared\TenantContextService;
use Closure;
use Illuminate\Http\Request;

class LoadTenantContext
{
    public function __construct(
        private readonly TenantContextService $tenantContextService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$this->tenantContextService->multipleTenantsAllowed()) {
            $tenant = $user->tenants()
                ->orderByPivot('joined_at')
                ->first();

            $this->tenantContextService->setCurrentTenant($tenant);

            return $next($request);
        }

        $tenantId = session($this->tenantContextService->tenantSessionKey());

        if ($tenantId) {
            $tenant = $user->tenants()
                ->where('tenants.id', $tenantId)
                ->first();

            if ($tenant) {
                $this->tenantContextService->setCurrentTenant($tenant);
            }
        }

        return $next($request);
    }
}
