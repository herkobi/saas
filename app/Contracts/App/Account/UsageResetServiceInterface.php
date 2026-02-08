<?php

/**
 * Usage Reset Service Interface
 *
 * This interface defines the contract for usage reset operations.
 *
 * @package    App\Contracts\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Feature;
use App\Models\TenantUsage;
use Illuminate\Support\Collection;

/**
 * Interface for usage reset service implementations.
 */
interface UsageResetServiceInterface
{
    /**
     * Reset all expired usage records.
     *
     * @return int Number of reset records
     */
    public function resetExpiredUsages(): int;

    /**
     * Reset a single usage record.
     *
     * @param TenantUsage $usage The usage record to reset
     * @return void
     */
    public function resetUsage(TenantUsage $usage): void;

    /**
     * Initialize usage tracking for a tenant and feature.
     *
     * @param string $tenantId The tenant ID
     * @param Feature $feature The feature to track
     * @return TenantUsage
     */
    public function initializeUsageForTenant(string $tenantId, Feature $feature): TenantUsage;

    /**
     * Get all metered features.
     *
     * @return Collection
     */
    public function getMeteredFeatures(): Collection;
}
