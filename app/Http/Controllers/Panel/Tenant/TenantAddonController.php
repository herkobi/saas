<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Tenant;

use App\Services\Panel\Addon\TenantAddonService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Tenant\ExtendAddonRequest;
use App\Models\Addon;
use App\Models\Tenant;
use App\Models\TenantAddon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantAddonController extends Controller
{
    public function __construct(
        protected TenantAddonService $tenantAddonService
    ) {}

    /**
     * Display tenant's addons.
     */
    public function index(Tenant $tenant): Response
    {
        $addons = $this->tenantAddonService->getByTenant($tenant);
        $activeAddons = $this->tenantAddonService->getActiveTenantAddons($tenant);

        return Inertia::render('panel/Tenants/Addons', [
            'tenant' => $tenant,
            'addons' => $addons,
            'activeAddons' => $activeAddons,
        ]);
    }

    /**
     * Extend a tenant's addon expiry.
     */
    public function extend(ExtendAddonRequest $request, Tenant $tenant, string $addonId): RedirectResponse
    {
        $tenantAddon = TenantAddon::where('tenant_id', $tenant->id)
            ->where('addon_id', $addonId)
            ->firstOrFail();

        $this->tenantAddonService->extendAddon(
            $tenantAddon,
            (int) $request->validated('days'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.addons.index', $tenant)
            ->with('success', 'Eklenti süresi başarıyla uzatıldı.');
    }

    /**
     * Cancel a tenant's addon.
     */
    public function cancel(Request $request, Tenant $tenant, string $addonId): RedirectResponse
    {
        $addon = Addon::findOrFail($addonId);

        $result = $this->tenantAddonService->cancelAddon(
            $tenant,
            $addon,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        if (!$result) {
            return redirect()
                ->route('panel.tenants.addons.index', $tenant)
                ->with('error', 'Aktif eklenti bulunamadı.');
        }

        return redirect()
            ->route('panel.tenants.addons.index', $tenant)
            ->with('success', 'Eklenti başarıyla iptal edildi.');
    }
}
