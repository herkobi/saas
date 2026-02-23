<?php

/**
 * Tenant Helper Functions
 *
 * Global helper functions for tenant context management.
 *
 * @package    App\Helpers
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Services\Shared\TenantContextService;
use App\Models\Tenant;

if (!function_exists('current_tenant')) {
    /**
     * Get the current tenant from the tenant context service.
     *
     * @return Tenant|null
     */
    function current_tenant(): ?Tenant
    {
        return app(TenantContextService::class)->currentTenant();
    }
}
