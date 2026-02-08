<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddonType;
use App\Enums\FeatureType;
use App\Enums\ResetPeriod;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUsage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TenantUsageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tenants = Tenant::with([
                'subscription.price.plan',
                'featureOverrides',
                'addons',
                'users',
            ])->get();

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                $plan = $tenant->subscription?->price?->plan;
                if (! $plan) {
                    continue;
                }

                $planFeatures = $plan->features()->get(); // pivot: value

                foreach ($planFeatures as $feature) {
                    if (! in_array($feature->type, [FeatureType::LIMIT, FeatureType::METERED], true)) {
                        continue;
                    }

                    $limit = $this->getEffectiveLimitString($tenant, $plan, $feature);
                    $used = $this->calculateUsed($tenant, $feature, $limit);

                    TenantUsage::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'feature_id' => $feature->id,
                        ],
                        [
                            'used' => $used,
                            'cycle_ends_at' => $this->cycleEndsAt($feature->reset_period),
                        ]
                    );
                }
            }
        });
    }

    private function getEffectiveLimitString(Tenant $tenant, Plan $plan, Feature $feature): string
    {
        // override
        $override = $tenant->featureOverrides()->where('feature_id', $feature->id)->first();
        if ($override) {
            return (string) $override->value;
        }

        // plan pivot
        $planFeature = $plan->features()->where('features.id', $feature->id)->first();
        $base = $planFeature ? (string) $planFeature->pivot->value : '0';

        // addons (aktif)
        $addons = $tenant->addons()
            ->where('feature_id', $feature->id)
            ->where('tenant_addons.is_active', true)
            ->where(function ($q) {
                $q->whereNull('tenant_addons.expires_at')
                    ->orWhere('tenant_addons.expires_at', '>', now());
            })
            ->get();

        foreach ($addons as $addon) {
            if ($addon->addon_type === AddonType::UNLIMITED) {
                return '-1';
            }

            if ($addon->addon_type === AddonType::INCREMENT) {
                $qty = (int) ($addon->pivot->quantity ?? 1);
                $base = (string) ((int) $base + ((int) $addon->value * $qty));
            }
        }

        return $base;
    }

    private function calculateUsed(Tenant $tenant, Feature $feature, string $limit): string
    {
        // users: gerçek kullanıcı sayısı
        if ($feature->code === 'users') {
            return (string) $tenant->users()->count();
        }

        // limitsiz
        if ($limit === '-1') {
            return (string) rand(50, 500);
        }

        $l = max(0, (int) $limit);

        // demo: bazı tenantlarda limite yakın göster (past_due/expired daha agresif)
        $status = $tenant->status?->value ?? null;
        $pctMin = in_array($status, ['past_due', 'expired'], true) ? 75 : 15;
        $pctMax = in_array($status, ['past_due', 'expired'], true) ? 99 : 95;

        $pct = rand($pctMin, $pctMax) / 100;

        return (string) max(0, (int) floor($l * $pct));
    }

    private function cycleEndsAt(?ResetPeriod $resetPeriod): ?\Carbon\CarbonInterface
    {
        if (! $resetPeriod) {
            return null;
        }

        return match ($resetPeriod) {
            ResetPeriod::Daily => now()->addDay(),
            ResetPeriod::Weekly => now()->addWeek(),
            ResetPeriod::Monthly => now()->addMonth(),
            ResetPeriod::Yearly => now()->addYear(),
            default => null,
        };
    }
}
