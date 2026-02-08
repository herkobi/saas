<?php

/**
 * Has Storage Paths Trait
 *
 * Provides storage path helpers for models that need
 * to manage files in tenant or panel contexts.
 *
 * @package    App\Traits
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tenant;

/**
 * Trait for models that need storage path helpers.
 *
 * Provides methods for generating consistent storage paths
 * for documents, uploads, and other user files.
 */
trait HasStoragePaths
{
    /**
     * Get the user files storage path.
     *
     * @param Tenant|null $tenant
     * @return string
     */
    public function getUserStoragePath(?Tenant $tenant = null): string
    {
        $basePath = $tenant
            ? 'tenant_' . $tenant->code
            : 'panel';

        return $basePath . '/users/' . $this->id;
    }

    /**
     * Get the documents storage path.
     *
     * @param Tenant|null $tenant
     * @return string
     */
    public function getDocumentsStoragePath(?Tenant $tenant = null): string
    {
        return $this->getUserStoragePath($tenant) . '/documents';
    }

    /**
     * Get the uploads storage path.
     *
     * @param Tenant|null $tenant
     * @return string
     */
    public function getUploadsStoragePath(?Tenant $tenant = null): string
    {
        return $this->getUserStoragePath($tenant) . '/uploads';
    }
}
