<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\GracePeriod;
use App\Enums\PlanInterval;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $free = Plan::updateOrCreate(
                ['slug' => 'free'],
                [
                    'name' => 'Ücretsiz',
                    'description' => 'Başlangıç paketi',
                    'tenant_id' => null,
                    'is_free' => true,
                    'is_active' => true,
                    'is_public' => true,
                    'grace_period_days' => 0,
                    'grace_period_policy' => GracePeriod::RESTRICTED,
                ]
            );

            $standard = Plan::updateOrCreate(
                ['slug' => 'standard'],
                [
                    'name' => 'Standart',
                    'description' => 'Ücretli standart plan',
                    'tenant_id' => null,
                    'is_free' => false,
                    'is_active' => true,
                    'is_public' => true,
                    'grace_period_days' => 7,
                    'grace_period_policy' => GracePeriod::RESTRICTED,
                ]
            );

            $pro = Plan::updateOrCreate(
                ['slug' => 'pro'],
                [
                    'name' => 'Pro',
                    'description' => 'Bazı özellikler limitsiz',
                    'tenant_id' => null,
                    'is_free' => false,
                    'is_active' => true,
                    'is_public' => true,
                    'grace_period_days' => 7,
                    'grace_period_policy' => GracePeriod::RESTRICTED,
                ]
            );

            $business = Plan::updateOrCreate(
                ['slug' => 'business'],
                [
                    'name' => 'Business',
                    'description' => 'Orta ölçek ekipler için',
                    'tenant_id' => null,
                    'is_free' => false,
                    'is_active' => true,
                    'is_public' => true,
                    'grace_period_days' => 10,
                    'grace_period_policy' => GracePeriod::RESTRICTED,
                ]
            );

            $enterprise = Plan::updateOrCreate(
                ['slug' => 'enterprise'],
                [
                    'name' => 'Enterprise',
                    'description' => 'Kurumsal seviye',
                    'tenant_id' => null,
                    'is_free' => false,
                    'is_active' => true,
                    'is_public' => true,
                    'grace_period_days' => 14,
                    'grace_period_policy' => GracePeriod::RESTRICTED,
                ]
            );

            $this->upsertPrice($free, '0.00');
            $this->upsertPrice($standard, '450.00');
            $this->upsertPrice($pro, '650.00');
            $this->upsertPrice($business, '950.00');
            $this->upsertPrice($enterprise, '1900.00');

            $features = Feature::query()->pluck('id', 'code');

            $this->syncFeatures($free, [
                ($features['users'] ?? null) => '1',
                ($features['storage_gb'] ?? null) => '1',
                ($features['api_access'] ?? null) => '0',
                ($features['priority_support'] ?? null) => '0',
                ($features['api_requests'] ?? null) => '0',
                ($features['email_sends'] ?? null) => '10',
            ]);

            $this->syncFeatures($standard, [
                ($features['users'] ?? null) => '5',
                ($features['storage_gb'] ?? null) => '20',
                ($features['api_access'] ?? null) => '1',
                ($features['priority_support'] ?? null) => '0',
                ($features['api_requests'] ?? null) => '10000',
                ($features['email_sends'] ?? null) => '200',
            ]);

            $this->syncFeatures($pro, [
                ($features['users'] ?? null) => '-1',
                ($features['storage_gb'] ?? null) => '200',
                ($features['api_access'] ?? null) => '1',
                ($features['priority_support'] ?? null) => '1',
                ($features['api_requests'] ?? null) => '50000',
                ($features['email_sends'] ?? null) => '1000',
            ]);

            $this->syncFeatures($business, [
                ($features['users'] ?? null) => '25',
                ($features['storage_gb'] ?? null) => '500',
                ($features['api_access'] ?? null) => '1',
                ($features['priority_support'] ?? null) => '1',
                ($features['api_requests'] ?? null) => '200000',
                ($features['email_sends'] ?? null) => '5000',
            ]);

            $this->syncFeatures($enterprise, [
                ($features['users'] ?? null) => '-1',
                ($features['storage_gb'] ?? null) => '-1',
                ($features['api_access'] ?? null) => '1',
                ($features['priority_support'] ?? null) => '1',
                ($features['api_requests'] ?? null) => '-1',
                ($features['email_sends'] ?? null) => '-1',
            ]);
        });
    }

    private function upsertPrice(Plan $plan, string $price): void
    {
        PlanPrice::updateOrCreate(
            [
                'plan_id' => $plan->id,
                'currency' => 'TRY',
                'interval' => PlanInterval::MONTH,
                'interval_count' => 1,
            ],
            [
                'price' => $price,
                'trial_days' => 0,
            ]
        );
    }

    /**
     * @param array<string|null,string> $map
     */
    private function syncFeatures(Plan $plan, array $map): void
    {
        $rows = [];
        foreach ($map as $featureId => $value) {
            if (! $featureId) {
                continue;
            }
            $rows[$featureId] = ['value' => $value];
        }

        $plan->features()->sync($rows);
    }
}
