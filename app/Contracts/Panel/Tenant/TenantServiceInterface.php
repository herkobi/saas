<?php

/**
 * Panel Tenant Service Interface Contract
 *
 * This interface defines the contract for panel tenant service
 * implementations, providing methods for tenant management
 * within the panel domain.
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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for panel tenant service implementations.
 *
 * Defines the contract for managing tenants from the panel,
 * including listing, filtering, and update operations with audit context.
 */
interface TenantServiceInterface
{
    /**
     * Get paginated list of tenants with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters (search, status, plan, dates)
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated tenant results
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all tenants without pagination.
     *
     * @return Collection Collection of all tenants
     */
    public function getAll(): Collection;

    /**
     * Get all active tenants without pagination.
     *
     * Active tenants are those with valid subscriptions (ACTIVE, TRIALING, CANCELED, or PAST_DUE).
     *
     * @return Collection Collection of active tenants
     */
    public function getActive(): Collection;

    /**
     * Find a tenant by its ID.
     *
     * @param string $id The tenant ULID
     * @return Tenant|null The tenant or null if not found
     */
    public function findById(string $id): ?Tenant;

    /**
     * Find a tenant by its unique code.
     *
     * @param string $code The tenant code
     * @return Tenant|null The tenant or null if not found
     */
    public function findByCode(string $code): ?Tenant;

    /**
     * Update a tenant's information.
     *
     * @param Tenant $tenant The tenant to update
     * @param array<string, mixed> $data The data to update
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Tenant The updated tenant instance
     */
    public function update(Tenant $tenant, array $data, User $admin, string $ipAddress, string $userAgent): Tenant;

    /**
     * Get paginated activities for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get activities for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated activity results
     */
    public function getActivities(Tenant $tenant, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all users belonging to a tenant.
     *
     * @param Tenant $tenant The tenant to get users for
     * @return Collection Collection of tenant users
     */
    public function getUsers(Tenant $tenant): Collection;

    /**
     * Get paginated payments for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get payments for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated payment results
     */
    public function getPayments(Tenant $tenant, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get statistics for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get statistics for
     * @return array<string, mixed> Array of statistics data
     */
    public function getStatistics(Tenant $tenant): array;

    /**
     * Get recent activities across all tenants.
     *
     * @param int $limit Maximum number of results
     * @return Collection Collection of recent activities with user relation
     */
    public function getRecentActivities(int $limit = 15): Collection;
}
