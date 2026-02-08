<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Addon;

use App\Contracts\Panel\Addon\AddonServiceInterface;
use App\Enums\FeatureType;
use App\Helpers\CurrencyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Addon\StoreAddonRequest;
use App\Http\Requests\Panel\Addon\UpdateAddonRequest;
use App\Models\Addon;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddonController extends Controller
{
    public function __construct(
        protected AddonServiceInterface $addonService
    ) {}

    /**
     * Display a listing of addons.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'feature_id' => $request->input('feature_id'),
            'addon_type' => $request->input('addon_type'),
            'is_recurring' => $request->input('is_recurring'),
            'is_active' => $request->input('is_active'),
            'is_public' => $request->input('is_public'),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $perPage = (int) $request->input('per_page', 15);
        $addons = $this->addonService->getPaginated($filters, $perPage);
        $features = Feature::active()->orderBy('name')->get();

        return Inertia::render('panel/addons.index', compact('addons', 'features', 'filters'));
    }

    /**
     * Show the form for creating a new addon.
     */
    public function create(): Response
    {
        $features = Feature::query()
            ->select(['id', 'name', 'code', 'type'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $allowedAddonTypesByFeatureType = collect(FeatureType::cases())
            ->mapWithKeys(fn (FeatureType $type) => [
                $type->value => array_map(fn ($addonType) => $addonType->value, $type->allowedAddonTypes()),
            ])
            ->toArray();

        $systemCurrency = CurrencyHelper::defaultCode();

        return Inertia::render('panel/addons.create', compact('features', 'allowedAddonTypesByFeatureType', 'systemCurrency'));
    }


    /**
     * Store a newly created addon.
     */
    public function store(StoreAddonRequest $request): RedirectResponse
    {
        $this->addonService->create(
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.plans.addons.index')
            ->with('success', 'Eklenti başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the addon.
     */
    public function edit(Addon $addon): Response
    {
        $features = Feature::query()
            ->select(['id', 'name', 'code', 'type'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $allowedAddonTypesByFeatureType = collect(FeatureType::cases())
            ->mapWithKeys(fn (FeatureType $type) => [
                $type->value => array_map(fn ($addonType) => $addonType->value, $type->allowedAddonTypes()),
            ])
            ->toArray();

        $systemCurrency = CurrencyHelper::defaultCode();

        return Inertia::render('panel/addons.edit', compact('addon', 'features', 'allowedAddonTypesByFeatureType', 'systemCurrency'));
    }


    /**
     * Update the addon.
     */
    public function update(UpdateAddonRequest $request, Addon $addon): RedirectResponse
    {
        $this->addonService->update(
            $addon,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.plans.addons.index')
            ->with('success', 'Eklenti başarıyla güncellendi.');
    }

    /**
     * Remove the addon.
     */
    public function destroy(Request $request, Addon $addon): RedirectResponse
    {
        $this->addonService->delete(
            $addon,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.plans.addons.index')
            ->with('success', 'Eklenti başarıyla silindi.');
    }
}
