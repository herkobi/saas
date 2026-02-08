<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Account;

use App\Contracts\App\Account\AddonPurchaseServiceInterface;
use App\Events\TenantAddonCancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\PurchaseAddonRequest;
use App\Models\Addon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddonController extends Controller
{
    public function __construct(
        protected AddonPurchaseServiceInterface $addonPurchaseService
    ) {}

    /**
     * Display available addons and tenant's current addons.
     */
    public function index(): Response
    {
        $tenant = current_tenant();

        $availableAddons = $this->addonPurchaseService->getAvailableAddons($tenant);
        $currentAddons = $tenant->addons()
            ->with('feature')
            ->wherePivot('is_active', true)
            ->get();

        return Inertia::render('app/Account/Addons/Index', compact('availableAddons', 'currentAddons'));
    }

    /**
     * Purchase an addon.
     */
    public function purchase(PurchaseAddonRequest $request): RedirectResponse
    {
        $tenant = current_tenant();
        $addon = Addon::findOrFail($request->input('addon_id'));
        $quantity = $request->input('quantity');

        // Check if addon is active and public
        if (!$addon->is_active || !$addon->is_public) {
            return redirect()
                ->route('app.account.addons.index')
                ->with('error', 'Bu eklenti şu anda satın alınamıyor.');
        }

        // Create checkout session
        $checkout = $this->addonPurchaseService->purchaseAddon($tenant, $addon, $quantity);

        // Redirect to checkout/payment page
        return redirect()
            ->route('app.account.checkout.processing', ['checkoutId' => $checkout->id])
            ->with('info', 'Ödeme sayfasına yönlendiriliyorsunuz...');
    }

    /**
     * Cancel an addon from tenant.
     */
    public function cancel(Request $request, string $addonId): RedirectResponse
    {
        $tenant = current_tenant();
        $addon = Addon::findOrFail($addonId);

        $pivotRecord = $tenant->addons()
            ->where('addon_id', $addon->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$pivotRecord) {
            return redirect()
                ->route('app.account.addons.index')
                ->with('error', 'Aktif eklenti bulunamadı.');
        }

        $tenantAddon = $pivotRecord->pivot;

        $tenant->addons()->updateExistingPivot($addon->id, [
            'is_active' => false,
        ]);

        TenantAddonCancelled::dispatch(
            $tenant,
            $tenantAddon,
            $request->ip() ?? '0.0.0.0',
            $request->userAgent() ?? 'unknown'
        );

        return redirect()
            ->route('app.account.addons.index')
            ->with('success', 'Eklenti iptal edildi.');
    }
}
