<?php

/**
 * Storage Service Interface
 *
 * Defines the contract for storage management operations
 * shared across panel and tenant contexts.
 *
 * @package    App\Contracts\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Shared;

use App\Models\Tenant;
use App\Models\User;

/**
 * Interface for storage service operations.
 *
 * Provides methods for managing tenant and user storage paths.
 */
interface StorageServiceInterface
{
    /**
     * Get the base path for a tenant.
     *
     * @param Tenant $tenant
     * @return string
     */
    public function getTenantBasePath(Tenant $tenant): string;

    /**
     * Get the base path for panel storage.
     *
     * @return string
     */
    public function getPanelBasePath(): string;

    /**
     * Get the user directory path.
     *
     * @param User $user
     * @param Tenant|null $tenant
     * @return string
     */
    public function getUserPath(User $user, ?Tenant $tenant = null): string;

    /**
     * Delete all storage directories for a tenant.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function deleteTenantDirectories(Tenant $tenant): void;

    /**
     * Delete all storage directories for a user.
     *
     * @param User $user
     * @param Tenant|null $tenant
     * @return void
     */
    public function deleteUserDirectories(User $user, ?Tenant $tenant = null): void;
}
