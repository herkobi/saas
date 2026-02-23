<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Shared\TenantContextService;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMemberActive
{
    public function __construct(
        private readonly TenantContextService $tenantContextService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $this->tenantContextService->currentTenant();

        if (! $user || ! $tenant) {
            return $next($request);
        }

        $pivotStatus = $tenant->users()
            ->where('users.id', $user->id)
            ->first()
            ?->pivot
            ?->status;

        if ($pivotStatus === null) {
            return $next($request);
        }

        $status = UserStatus::from((int) $pivotStatus);

        if ($status === UserStatus::PASSIVE) {
            if ($this->tenantContextService->multipleTenantsAllowed()) {
                $activeTenant = $user->tenants()
                    ->wherePivot('status', UserStatus::ACTIVE->value)
                    ->orderByPivot('joined_at')
                    ->first();

                if ($activeTenant) {
                    $this->tenantContextService->setCurrentTenant($activeTenant);

                    return redirect()
                        ->route('app.dashboard')
                        ->with('warning', 'Önceki hesaptaki erişiminiz kısıtlanmış. Aktif hesabınıza yönlendirildiniz.');
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bu hesaptaki erişiminiz kısıtlanmış. Lütfen hesap sahibiyle iletişime geçin.',
                ], 422);
            }

            return redirect()
                ->route('app.profile.edit')
                ->with('error', 'Bu hesaptaki erişiminiz kısıtlanmış. Lütfen hesap sahibiyle iletişime geçin.');
        }

        return $next($request);
    }
}
