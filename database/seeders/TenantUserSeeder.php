<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddonType;
use App\Enums\TenantUserRole;
use App\Enums\UserType;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tenants = Tenant::with(['subscription.price.plan', 'featureOverrides', 'addons', 'users'])->get();

            $usersFeature = Feature::where('code', 'users')->first();
            if (! $usersFeature) {
                return;
            }

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                $plan = $tenant->subscription?->price?->plan;
                if (! $plan) {
                    continue;
                }

                $limit = $this->getEffectiveLimitString($tenant, $plan, $usersFeature);
                $targetTotalUsers = $this->targetUsersForDemo($limit);

                $currentTotal = $tenant->users()->count();
                $need = max(0, $targetTotalUsers - $currentTotal);

                for ($i = 0; $i < $need; $i++) {
                    $email = 'staff+'.Str::lower($tenant->code).'+'.($i + 1).'@tenant.com';

                    $staff = User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => ($tenant->account['title'] ?? 'Tenant').' Staff '.($i + 1),
                            'password' => Hash::make('password'),
                            'user_type' => UserType::TENANT,
                            'email_verified_at' => now(),
                        ]
                    );

                    if (! $tenant->users()->where('user_id', $staff->id)->exists()) {
                        $tenant->users()->attach($staff->id, [
                            'role' => TenantUserRole::STAFF->value,
                            'joined_at' => now()->subDays(rand(1, 120)),
                        ]);
                    }
                }
            }
        });
    }

    private function targetUsersForDemo(string $limit): int
    {
        if ($limit === '-1') {
            return rand(8, 18);
        }

        $l = max(1, (int) $limit);
        $min = max(1, (int) floor($l * 0.4));
        $max = max($min, (int) floor($l * 0.9));

        return rand($min, $max);
    }

    private function getEffectiveLimitString(Tenant $tenant, Plan $plan, Feature $feature): string
    {
        // 1) override
        $override = $tenant->featureOverrides()->where('feature_id', $feature->id)->first();
        if ($override) {
            return (string) $override->value;
        }

        // 2) plan pivot
        $planFeature = $plan->features()->where('features.id', $feature->id)->first();
        $base = $planFeature ? (string) $planFeature->pivot->value : '0';

        // 3) addons (aktif)
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
}
