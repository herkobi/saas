<?php

/**
 * Feature Controller
 *
 * This controller handles feature management operations including
 * CRUD operations for plan features.
 *
 * @package    App\Http\Controllers\Panel\Plan
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Plan;

use App\Contracts\Panel\Plan\FeatureServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Plan\StoreFeatureRequest;
use App\Http\Requests\Panel\Plan\UpdateFeatureRequest;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for feature management.
 *
 * Provides CRUD operations for managing plan features.
 */
class FeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param FeatureServiceInterface $featureService Service for feature operations
     */
    public function __construct(
        private readonly FeatureServiceInterface $featureService
    ) {}

    /**
     * Display a listing of features.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'type', 'is_active', 'sort', 'direction']);

        $perPage = (int) $request->get('per_page', 15);
        $allowedPerPages = [15, 30, 50, 100];
        if (!in_array($perPage, $allowedPerPages, true)) {
            $perPage = 15;
        }

        return Inertia::render('panel/plans.features.index', [
            'features' => $this->featureService->getPaginated($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new feature.
     *
     * @return View
     */
    public function create(): Response
    {
        return Inertia::render('panel/plans.features.create');
    }

    /**
     * Store a newly created feature.
     *
     * @param StoreFeatureRequest $request
     * @return RedirectResponse
     */
    public function store(StoreFeatureRequest $request): RedirectResponse
    {
        $this->featureService->create(
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.plans.features.index')
            ->with('success', 'Özellik oluşturuldu.');
    }

    /**
     * Show the form for editing the specified feature.
     *
     * @param Feature $feature
     * @return View
     */
    public function edit(Feature $feature): Response
    {
        // View tarafında $feature->plans kullanımı varsa tek seferde yükle
        $feature->load('plans');

        return Inertia::render('panel/plans.features.edit', [
            'feature' => $feature,
        ]);
    }

    /**
     * Update the specified feature.
     *
     * @param UpdateFeatureRequest $request
     * @param Feature $feature
     * @return RedirectResponse
     */
    public function update(UpdateFeatureRequest $request, Feature $feature): RedirectResponse
    {
        $this->featureService->update(
            $feature,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Özellik güncellendi.');
    }

    /**
     * Remove the specified feature.
     *
     * @param Request $request
     * @param Feature $feature
     * @return RedirectResponse
     */
    public function destroy(Request $request, Feature $feature): RedirectResponse
    {
        $this->featureService->delete(
            $feature,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Özellik silindi.');
    }
}
