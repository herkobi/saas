<?php

/**
 * Panel Tenant Feature Service Interface Contract
 *
 * This interface defines the contract for panel tenant feature
 * service implementations, providing methods for feature override
 * management within the panel domain.
 *
 * @package    App\Contracts\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Panel\Tenant;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Interface for panel tenant feature service implementations.
 *
 * Defines the contract for managing tenant-specific feature overrides
 * from the panel, including sync and removal operations with audit context.
 */
interface TenantFeatureServiceInterface
{
    /**
     * Get all feature overrides for a tenant.
     *
     * @param Tenant $tenant The tenant to get overrides for
     * @return Collection Collection of feature overrides
     */
    public function getOverrides(Tenant $tenant): Collection;

    /**
     * Get plan features for a tenant's current subscription.
     *
     * @param Tenant $tenant The tenant to get plan features for
     * @return Collection Collection of plan features with values
     */
    public function getPlanFeatures(Tenant $tenant): Collection;

    /**
     * Get effective features for a tenant (plan + overrides merged).
     *
     * @param Tenant $tenant The tenant to get effective features for
     * @return Collection Collection of effective features
     */
    public function getEffectiveFeatures(Tenant $tenant): Collection;

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
    ): void;

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
    ): void;

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
    ): void;
}
