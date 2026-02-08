<?php

/**
 * Storage Service
 *
 * Handles all storage-related operations including path
 * management and cleanup for tenants and users.
 *
 * @package    App\Services\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Shared;

use App\Contracts\Shared\StorageServiceInterface;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Storage Service
 *
 * Service implementation for managing tenant and user
 * storage paths across different disks.
 */
class StorageService implements StorageServiceInterface
{
    /**
     * Get the base path for a tenant.
     *
     * @param Tenant $tenant
     * @return string
     */
    public function getTenantBasePath(Tenant $tenant): string
    {
        return 'tenant_' . $tenant->code;
    }

    /**
     * Get the base path for panel storage.
     *
     * @return string
     */
    public function getPanelBasePath(): string
    {
        return 'panel';
    }

    /**
     * Get the user directory path.
     *
     * @param User $user
     * @param Tenant|null $tenant
     * @return string
     */
    public function getUserPath(User $user, ?Tenant $tenant = null): string
    {
        $basePath = $tenant
            ? $this->getTenantBasePath($tenant)
            : $this->getPanelBasePath();

        return $basePath . '/users/' . $user->id;
    }

    /**
     * Delete all storage directories for a tenant.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function deleteTenantDirectories(Tenant $tenant): void
    {
        foreach (['local', 'public'] as $disk) {
            Storage::disk($disk)->deleteDirectory($this->getTenantBasePath($tenant));
        }
    }

    /**
     * Delete all storage directories for a user.
     *
     * @param User $user
     * @param Tenant|null $tenant
     * @return void
     */
    public function deleteUserDirectories(User $user, ?Tenant $tenant = null): void
    {
        foreach (['local', 'public'] as $disk) {
            Storage::disk($disk)->deleteDirectory($this->getUserPath($user, $tenant));
        }
    }
}
