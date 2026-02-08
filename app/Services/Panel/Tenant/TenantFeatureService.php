<?php

/**
 * Panel Tenant Feature Service
 *
 * This service handles tenant feature override management operations for the panel,
 * including sync, removal, and effective feature calculation.
 *
 * @package    App\Services\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Tenant;

use App\Contracts\Panel\Tenant\TenantFeatureServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Events\PanelTenantFeatureOverrideUpdated;
use App\Models\Tenant;
use App\Models\TenantFeature;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service for managing tenant feature overrides from the panel.
 *
 * Provides methods for feature override CRUD operations with comprehensive
 * audit logging and event dispatching.
 */
class TenantFeatureService implements TenantFeatureServiceInterface
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    /**
     * Get all feature overrides for a tenant.
     *
     * @param Tenant $tenant The tenant to get overrides for
     * @return Collection Collection of feature overrides
     */
    public function getOverrides(Tenant $tenant): Collection
    {
        return $this->withTenantContext($tenant, function () {
            return TenantFeature::with('feature')->get();
        });
    }

    /**
     * Get plan features for a tenant's current subscription.
     *
     * @param Tenant $tenant The tenant to get plan features for
     * @return Collection Collection of plan features with values
     */
    public function getPlanFeatures(Tenant $tenant): Collection
    {
        $plan = $tenant->currentPlan();

        if (!$plan) {
            return collect();
        }

        return $plan->features()
            ->withPivot('value')
            ->get()
            ->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'slug' => $feature->slug,
                    'type' => $feature->type->value,
                    'value' => $feature->pivot->value,
                    'source' => 'plan',
                ];
            });
    }

    /**
     * Get effective features for a tenant (plan + overrides merged).
     *
     * @param Tenant $tenant The tenant to get effective features for
     * @return Collection Collection of effective features
     */
    public function getEffectiveFeatures(Tenant $tenant): Collection
    {
        $planFeatures = $this->getPlanFeatures($tenant)->keyBy('id');
        $overrides = $this->getOverrides($tenant)->keyBy('feature_id');

        return $planFeatures->map(function ($feature) use ($overrides) {
            $featureId = $feature['id'];

            if ($overrides->has($featureId)) {
                $override = $overrides->get($featureId);

                return [
                    'id' => $featureId,
                    'name' => $feature['name'],
                    'slug' => $feature['slug'],
                    'type' => $feature['type'],
                    'value' => $override->value,
                    'plan_value' => $feature['value'],
                    'source' => 'override',
                    'override_id' => $override->id,
                ];
            }

            return [
                'id' => $featureId,
                'name' => $feature['name'],
                'slug' => $feature['slug'],
                'type' => $feature['type'],
                'value' => $feature['value'],
                'plan_value' => $feature['value'],
                'source' => 'plan',
                'override_id' => null,
            ];
        })->values();
    }

    /**
     * Sync feature overrides for a tenant.
     *
     * @param Tenant $tenant The tenant to sync overrides for
     * @param array<string, mixed> $overrides Array of feature_id => value pairs
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function syncOverrides(
        Tenant $tenant,
        array $overrides,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): void {
        $oldOverrides = $this->getOverrides($tenant)->pluck('value', 'feature_id')->toArray();

        $this->withTenantContext($tenant, function () use ($overrides) {
            foreach ($overrides as $featureId => $value) {
                TenantFeature::updateOrCreate(
                    ['feature_id' => $featureId],
                    ['value' => $value]
                );
            }

            $existingFeatureIds = array_keys($overrides);
            TenantFeature::whereNotIn('feature_id', $existingFeatureIds)->delete();
        });

        $newOverrides = $this->getOverrides($tenant)->pluck('value', 'feature_id')->toArray();

        PanelTenantFeatureOverrideUpdated::dispatch(
            $tenant,
            $oldOverrides,
            $newOverrides,
            'sync',
            $admin,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Remove a specific feature override for a tenant.
     *
     * @param Tenant $tenant The tenant to remove override from
     * @param string $featureId The feature ID to remove override for
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function removeOverride(
        Tenant $tenant,
        string $featureId,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): void {
        $oldOverrides = $this->getOverrides($tenant)->pluck('value', 'feature_id')->toArray();

        $this->withTenantContext($tenant, function () use ($featureId) {
            TenantFeature::where('feature_id', $featureId)->delete();
        });

        $newOverrides = $this->getOverrides($tenant)->pluck('value', 'feature_id')->toArray();

        PanelTenantFeatureOverrideUpdated::dispatch(
            $tenant,
            $oldOverrides,
            $newOverrides,
            'remove',
            $admin,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Clear all feature overrides for a tenant.
     *
     * @param Tenant $tenant The tenant to clear overrides for
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function clearAllOverrides(
        Tenant $tenant,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): void {
        $oldOverrides = $this->getOverrides($tenant)->pluck('value', 'feature_id')->toArray();

        $this->withTenantContext($tenant, function () {
            TenantFeature::query()->delete();
        });

        PanelTenantFeatureOverrideUpdated::dispatch(
            $tenant,
            $oldOverrides,
            [],
            'clear',
            $admin,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Execute a callback within a tenant context.
     *
     * @param Tenant $tenant The tenant to bind to the context
     * @param callable $callback The callback to execute
     * @return mixed The result of the callback
     */
    private function withTenantContext(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->tenantContextService->currentTenant();

        $sessionKey = $this->tenantContextService->tenantSessionKey();
        $previousTenantId = session()->has($sessionKey) ? session()->get($sessionKey) : null;

        $this->tenantContextService->setCurrentTenant($tenant);

        try {
            return $callback();
        } finally {
            if ($previousTenant instanceof Tenant) {
                $this->tenantContextService->setCurrentTenant($previousTenant);
            } else {
                $this->tenantContextService->forgetCurrentTenant();
            }

            if ($previousTenantId !== null) {
                session()->put($sessionKey, $previousTenantId);
            } else {
                session()->forget($sessionKey);
            }
        }
    }
}
