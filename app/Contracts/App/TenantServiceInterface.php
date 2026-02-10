<?php

declare(strict_types=1);

namespace App\Contracts\App;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

interface TenantServiceInterface
{
    /**
     * Get all tenants owned by a user.
     */
    public function getOwnedTenants(User $user): Collection;

    /**
     * Get all tenants the user belongs to (owned + member).
     */
    public function getAllTenants(User $user): Collection;

    /**
     * Check if the user can create a new tenant.
     */
    public function canCreate(User $user): bool;

    /**
     * Create a new tenant for the user.
     */
    public function create(User $user, array $data, string $ipAddress, string $userAgent): Tenant;

    /**
     * Switch the active tenant for the user.
     */
    public function switchTenant(User $user, string $tenantId): ?Tenant;
}
