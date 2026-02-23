<?php

namespace App\Services\App\Account;

use App\Services\Shared\TenantContextService;
use App\Enums\FeatureType;
use App\Events\TenantMeteredUsageReset;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\TenantUsage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UsageResetService
{
    public function __construct(
        private readonly TenantContextService $tenantContextService
    ) {}

    public function resetExpiredUsages(): int
    {
        $resetCount = 0;

        $expiredUsages = TenantUsage::withoutTenantScope()
            ->with(['tenant', 'feature'])
            ->whereNotNull('cycle_ends_at')
            ->where('cycle_ends_at', '<=', now())
            ->get()
            ->map(fn($item) => $item instanceof TenantUsage ? $item : TenantUsage::withoutTenantScope()->findOrFail($item->id));

        foreach ($expiredUsages as $usage) {
            $this->resetUsage($usage);
            $resetCount++;
        }

        return $resetCount;
    }

    public function resetUsage(TenantUsage $usage): void
    {
        $feature = $usage->feature;
        $previousUsed = (float) $usage->used;

        $newCycleEndsAt = $this->calculateNextCycleEnd($feature);

        if ($previousUsed <= 0) {
            $usage->update([
                'cycle_ends_at' => $newCycleEndsAt,
            ]);

            return;
        }

        $usage->update([
            'used' => 0,
            'cycle_ends_at' => $newCycleEndsAt,
        ]);

        TenantMeteredUsageReset::dispatch($usage, $previousUsed);
    }

    public function initializeUsageForTenant(string $tenantId, Feature $feature): TenantUsage
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant instanceof Tenant) {
            $usage = TenantUsage::withoutTenantScope()->newModelInstance();
            $usage->tenant_id = $tenantId;
            $usage->feature_id = $feature->id;
            $usage->used = 0;
            $usage->cycle_ends_at = $this->calculateNextCycleEnd($feature);
            $usage->save();

            return $usage;
        }

        return $this->withTenantContext($tenant, function () use ($feature) {
            return TenantUsage::create([
                'feature_id' => $feature->id,
                'used' => 0,
                'cycle_ends_at' => $this->calculateNextCycleEnd($feature),
            ]);
        });
    }

    public function getMeteredFeatures(): Collection
    {
        return Feature::query()
            ->where('type', FeatureType::METERED->value)
            ->get();
    }

    private function calculateNextCycleEnd(Feature $feature): Carbon
    {
        $resetPeriod = $feature->reset_period ?? 'monthly';

        return match ($resetPeriod) {
            'daily' => now()->addDay()->startOfDay(),
            'weekly' => now()->addWeek()->startOfWeek(),
            'monthly' => now()->addMonth()->startOfMonth(),
            'yearly' => now()->addYear()->startOfYear(),
            default => now()->addMonth()->startOfMonth(),
        };
    }

    private function withTenantContext(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->tenantContextService->currentTenant();

        $sessionKey = $this->tenantContextService->tenantSessionKey();
        $previousTenantId = session()->has($sessionKey) ? session()->get($sessionKey) : null;

        $this->tenantContextService->setCurrentTenant($tenant);

        try {
            return $callback();
        } finally {
            if ($previousTenant instanceof Tenant) {
                $this->tenantContextService->setCurrentTenant($previousTenant);
            } else {
                $this->tenantContextService->forgetCurrentTenant();
            }

            if ($previousTenantId !== null) {
                session()->put($sessionKey, $previousTenantId);
            } else {
                session()->forget($sessionKey);
            }
        }
    }
}
