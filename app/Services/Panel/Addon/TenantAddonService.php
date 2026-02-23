<?php

declare(strict_types=1);

namespace App\Services\Panel\Addon;

use App\Enums\PlanInterval;
use App\Models\Addon;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\User;
use Illuminate\Support\Collection;

class TenantAddonService
{
    public function getByTenant(Tenant $tenant): Collection
    {
        return $tenant->addons()->with('feature')->get()
            ->map(fn ($addon) => array_merge($addon->toArray(), [
                'addon_type_label' => $addon->addon_type->label(),
            ]));
    }

    public function getActiveTenantAddons(Tenant $tenant): Collection
    {
        return $tenant->addons()
            ->with('feature')
            ->wherePivot('is_active', true)
            ->where(function($q) {
                $q->whereNull('tenant_addons.expires_at')
                  ->orWhere('tenant_addons.expires_at', '>', now());
            })
            ->get();
    }

    public function assignToTenant(
        Tenant $tenant,
        Addon $addon,
        int $quantity,
        ?array $customPricing,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon {
        $data = [
            'quantity' => $quantity,
            'started_at' => now(),
            'is_active' => true,
        ];

        if ($customPricing) {
            $data['custom_price'] = $customPricing['price'] ?? null;
            $data['custom_currency'] = $customPricing['currency'] ?? null;
        }

        if ($addon->is_recurring) {
            $data['expires_at'] = $this->calculateExpiration($addon);
        }

        $existing = $tenant->addons()->where('addon_id', $addon->id)->first();

        if ($existing) {
            $tenant->addons()->updateExistingPivot($addon->id, $data);
            $tenantAddon = $tenant->addons()->where('addon_id', $addon->id)->first()->pivot;
        } else {
            $tenant->addons()->attach($addon->id, $data);
            $tenantAddon = $tenant->addons()->where('addon_id', $addon->id)->first()->pivot;
        }

        return $tenantAddon;
    }

    public function removeFromTenant(
        Tenant $tenant,
        Addon $addon,
        User $admin,
        string $ip,
        string $userAgent
    ): bool {
        return (bool) $tenant->addons()->detach($addon->id);
    }

    public function updateQuantity(
        TenantAddon $tenantAddon,
        int $quantity,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon {
        $tenantAddon->update(['quantity' => $quantity]);

        return $tenantAddon->fresh();
    }

    public function extendAddon(
        TenantAddon $tenantAddon,
        int $days,
        User $admin,
        string $ip,
        string $userAgent
    ): TenantAddon {
        $currentExpiry = $tenantAddon->expires_at ?? now();
        $newExpiry = $currentExpiry->copy()->addDays($days);

        $tenantAddon->update([
            'expires_at' => $newExpiry,
            'is_active' => true,
        ]);

        return $tenantAddon->fresh();
    }

    public function cancelAddon(
        Tenant $tenant,
        Addon $addon,
        User $admin,
        string $ip,
        string $userAgent
    ): bool {
        $existing = $tenant->addons()
            ->where('addon_id', $addon->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$existing) {
            return false;
        }

        $tenant->addons()->updateExistingPivot($addon->id, [
            'is_active' => false,
        ]);

        return true;
    }

    protected function calculateExpiration(Addon $addon): \Carbon\Carbon
    {
        $interval = $addon->interval;
        $count = $addon->interval_count ?? 1;

        return match ($interval) {
            PlanInterval::DAY => now()->addDays($count),
            PlanInterval::MONTH => now()->addMonths($count),
            PlanInterval::YEAR => now()->addYears($count),
            default => now()->addMonth(),
        };
    }
}
