<?php

/**
 * Tenant Feature Seeder
 *
 * Seeds tenant-specific feature overrides.
 * Demonstrates custom limits assigned to specific tenants beyond their plan defaults.
 *
 * @package    Database\Seeders
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Tenant;
use App\Models\TenantFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TenantFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $tenants = Tenant::with('subscription.price.plan')->get();
            $features = Feature::pluck('id', 'code');

            foreach ($tenants as $tenant) {
                $plan = $tenant->subscription?->price?->plan;

                if (! $plan) {
                    continue;
                }

                // Standard plan tenant'a özel 100GB storage limiti
                if ($plan->slug === 'standard') {
                    TenantFeature::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'feature_id' => $features['storage_gb'] ?? null,
                        ],
                        [
                            'value' => '100', // Plan default: 20GB, özel: 100GB
                        ]
                    );
                }

                // Free plan tenant'a özel 3 kullanıcı limiti
                if ($plan->slug === 'free') {
                    TenantFeature::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'feature_id' => $features['users'] ?? null,
                        ],
                        [
                            'value' => '3', // Plan default: 1, özel: 3
                        ]
                    );

                    // Free plan'a özel olarak API erişimi aç
                    TenantFeature::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'feature_id' => $features['api_access'] ?? null,
                        ],
                        [
                            'value' => '1', // Plan default: 0, özel: 1
                        ]
                    );
                }
            }
        });
    }
}
