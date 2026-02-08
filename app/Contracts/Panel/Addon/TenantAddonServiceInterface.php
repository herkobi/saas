<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Addon;

use App\Models\Addon;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface TenantAddonServiceInterface
{
    public function getByTenant(Tenant $tenant): Collection;

    public function getActiveTenantAddons(Tenant $tenant): Collection;

    public function assignToTenant(
        Tenant $tenant,
        Addon $addon,
        int $quantity,
        ?array $customPricing,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon;

    public function removeFromTenant(
        Tenant $tenant,
        Addon $addon,
        User $admin,
        string $ip,
        string $userAgent
    ): bool;

    public function updateQuantity(
        TenantAddon $tenantAddon,
        int $quantity,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon;

    public function extendAddon(
        TenantAddon $tenantAddon,
        int $days,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon;

    public function cancelAddon(
        Tenant $tenant,
        Addon $addon,
        User $admin,
        string $ip,
        string $userAgent
    ): bool;
}
