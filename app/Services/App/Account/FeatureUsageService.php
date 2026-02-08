<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Contracts\App\Account\FeatureUsageServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Enums\FeatureType;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\TenantUsage;
use Illuminate\Support\Collection;

class FeatureUsageService implements FeatureUsageServiceInterface
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    public function getAllFeatures(Tenant $tenant): Collection
    {
        $plan = $tenant->currentPlan();

        if (!$plan) {
            return collect();
        }

        $planFeatures = $plan->features()->withPivot('value')->get();
        $overrides = $tenant->featureOverrides()->with('feature')->get()->keyBy('feature_id');
        $usages = $tenant->usages()->get()->keyBy('feature_id');

        return $planFeatures->map(function ($feature) use ($overrides, $usages) {
            $override = $overrides->get($feature->id);
            $usage = $usages->get($feature->id);
            $effectiveValue = $override?->value ?? $feature->pivot->value;

            return [
                'id' => $feature->id,
                'name' => $feature->name,
                'slug' => $feature->slug,
                'description' => $feature->description,
                'type' => $feature->type->value,
                'type_label' => $feature->type->label(),
                'unit' => $feature->unit,
                'reset_period' => $feature->reset_period,
                'plan_value' => $feature->pivot->value,
                'effective_value' => $effectiveValue,
                'has_override' => $override !== null,
                'usage' => $this->formatUsage($feature, $effectiveValue, $usage),
            ];
        });
    }

    public function getFeatureUsage(Tenant $tenant, string $featureSlug): array
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature) {
            return ['error' => 'Özellik bulunamadı.'];
        }

        $plan = $tenant->currentPlan();

        if (!$plan) {
            return ['error' => 'Aktif plan bulunamadı.'];
        }

        $planFeature = $plan->features()->where('features.id', $feature->id)->first();

        if (!$planFeature) {
            return ['error' => 'Bu özellik mevcut planda tanımlı değil.'];
        }

        $override = $tenant->featureOverrides()
            ->where('feature_id', $feature->id)
            ->first();

        $usage = $tenant->usages()
            ->where('feature_id', $feature->id)
            ->first();

        $effectiveValue = $override?->value ?? $planFeature->pivot->value;

        return [
            'feature' => [
                'id' => $feature->id,
                'name' => $feature->name,
                'slug' => $feature->slug,
                'type' => $feature->type->value,
                'unit' => $feature->unit,
            ],
            'limit' => $effectiveValue,
            'used' => $usage?->used ?? 0,
            'remaining' => $this->calculateRemaining($feature, $effectiveValue, $usage),
            'percentage' => $this->calculatePercentage($feature, $effectiveValue, $usage),
            'reset_at' => $usage?->cycle_ends_at?->toISOString(),
        ];
    }

    public function getLimitFeatures(Tenant $tenant): Collection
    {
        return $this->getAllFeatures($tenant)
            ->filter(fn($f) => $f['type'] === FeatureType::LIMIT->value);
    }

    public function getMeteredFeatures(Tenant $tenant): Collection
    {
        return $this->getAllFeatures($tenant)
            ->filter(fn($f) => $f['type'] === FeatureType::METERED->value);
    }

    public function getBooleanFeatures(Tenant $tenant): Collection
    {
        return $this->getAllFeatures($tenant)
            ->filter(fn($f) => $f['type'] === FeatureType::FEATURE->value);
    }

    public function checkFeatureAccess(Tenant $tenant, string $featureSlug): bool
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature) {
            return false;
        }

        $plan = $tenant->currentPlan();

        if (!$plan) {
            return false;
        }

        $planFeature = $plan->features()->where('features.id', $feature->id)->first();

        if (!$planFeature) {
            return false;
        }

        $override = $tenant->featureOverrides()
            ->where('feature_id', $feature->id)
            ->first();

        $effectiveValue = $override?->value ?? $planFeature->pivot->value;

        if ($feature->type === FeatureType::FEATURE) {
            return filter_var($effectiveValue, FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }

    public function checkFeatureLimit(Tenant $tenant, string $featureSlug): array
    {
        $usage = $this->getFeatureUsage($tenant, $featureSlug);

        if (isset($usage['error'])) {
            return [
                'allowed' => false,
                'reason' => $usage['error'],
            ];
        }

        $feature = Feature::where('slug', $featureSlug)->first();

        if ($feature && $feature->type === FeatureType::FEATURE) {
            return [
                'allowed' => filter_var($usage['limit'], FILTER_VALIDATE_BOOLEAN),
                'reason' => 'Erişim kontrolü',
            ];
        }

        if ($usage['limit'] === 'unlimited' || $usage['limit'] === '-1') {
            return [
                'allowed' => true,
                'reason' => 'Sınırsız',
            ];
        }

        $remaining = $usage['remaining'];

        return [
            'allowed' => $remaining > 0,
            'reason' => $remaining > 0 ? 'Limit içinde' : 'Limit aşıldı',
            'remaining' => $remaining,
            'limit' => $usage['limit'],
            'used' => $usage['used'],
        ];
    }

    public function incrementUsage(Tenant $tenant, string $featureSlug, int $amount = 1): bool
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature || !in_array($feature->type, [FeatureType::LIMIT, FeatureType::METERED], true)) {
            return false;
        }

        return (bool) $this->withTenantContext($tenant, function () use ($feature, $amount) {
            $usage = TenantUsage::firstOrCreate(
                ['feature_id' => $feature->id],
                [
                    'used' => 0,
                    'cycle_ends_at' => $this->calculateResetDate($feature),
                ]
            );

            $usage->increment('used', $amount);

            return true;
        });
    }

    public function decrementUsage(Tenant $tenant, string $featureSlug, int $amount = 1): bool
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature) {
            return false;
        }

        return (bool) $this->withTenantContext($tenant, function () use ($feature, $amount) {
            $usage = TenantUsage::where('feature_id', $feature->id)->first();

            if (!$usage) {
                return false;
            }

            $newValue = max(0, $usage->used - $amount);
            $usage->update(['used' => $newValue]);

            return true;
        });
    }

    public function resetUsage(Tenant $tenant, string $featureSlug): bool
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature) {
            return false;
        }

        return (bool) $this->withTenantContext($tenant, function () use ($feature) {
            TenantUsage::where('feature_id', $feature->id)
                ->update([
                    'used' => 0,
                    'cycle_ends_at' => $this->calculateResetDate($feature),
                ]);

            return true;
        });
    }

    private function formatUsage(Feature $feature, string $effectiveValue, ?TenantUsage $usage): array
    {
        if ($feature->type === FeatureType::FEATURE) {
            return [
                'type' => 'boolean',
                'enabled' => filter_var($effectiveValue, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        $limit = $effectiveValue === 'unlimited' || $effectiveValue === '-1'
            ? null
            : (int) $effectiveValue;

        $used = $usage?->used ?? 0;

        return [
            'type' => $feature->type->value,
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit === null ? null : max(0, $limit - $used),
            'percentage' => $limit === null ? 0 : min(100, round(($used / $limit) * 100)),
            'is_unlimited' => $limit === null,
            'reset_at' => $usage?->cycle_ends_at?->toISOString(),
        ];
    }

    private function calculateRemaining(Feature $feature, string $effectiveValue, ?TenantUsage $usage): int|string
    {
        if ($effectiveValue === 'unlimited' || $effectiveValue === '-1') {
            return 'unlimited';
        }

        $limit = (int) $effectiveValue;
        $used = $usage?->used ?? 0;

        return max(0, $limit - $used);
    }

    private function calculatePercentage(Feature $feature, string $effectiveValue, ?TenantUsage $usage): float
    {
        if ($effectiveValue === 'unlimited' || $effectiveValue === '-1') {
            return 0;
        }

        $limit = (int) $effectiveValue;
        $used = $usage?->used ?? 0;

        if ($limit === 0) {
            return 0;
        }

        return min(100, round(($used / $limit) * 100, 2));
    }

    private function calculateResetDate(Feature $feature): ?\Carbon\Carbon
    {
        if (!$feature->reset_period) {
            return null;
        }

        return match ($feature->reset_period) {
            'daily' => now()->addDay()->startOfDay(),
            'weekly' => now()->addWeek()->startOfWeek(),
            'monthly' => now()->addMonth()->startOfMonth(),
            'yearly' => now()->addYear()->startOfYear(),
            default => null,
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
