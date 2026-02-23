<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Shared\TenantContextService;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwner
{
    public function __construct(
        private readonly TenantContextService $tenantContextService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContextService->currentTenant();
        $user = $request->user();

        if (!$tenant instanceof Tenant || !$user) {
            return redirect()->route('register');
        }

        if (!$tenant->isOwner($user)) {
            return $this->denyAccess();
        }

        return $next($request);
    }

    private function denyAccess(): RedirectResponse
    {
        return redirect()->back()->with('error', 'Bu işlem için yetkiniz yok.');
    }
}
