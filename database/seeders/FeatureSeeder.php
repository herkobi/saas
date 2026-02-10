<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FeatureType;
use App\Enums\ResetPeriod;
use App\Models\Feature;
use Illuminate\Database\Seeder;

final class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // LIMIT
            [
                'code' => 'tenants',
                'slug' => 'tenants',
                'name' => 'Tenant Limiti',
                'description' => 'Kullanıcının oluşturabileceği tenant sayısı',
                'type' => FeatureType::LIMIT,
                'unit' => 'adet',
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],
            [
                'code' => 'users',
                'slug' => 'users',
                'name' => 'Kullanıcı Limiti',
                'description' => 'Tenant içindeki kullanıcı sayısı',
                'type' => FeatureType::LIMIT,
                'unit' => 'adet',
                // ✅ NOT NULL: default veriyoruz
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],
            [
                'code' => 'storage_gb',
                'slug' => 'storage-gb',
                'name' => 'Depolama',
                'description' => 'Toplam depolama limiti',
                'type' => FeatureType::LIMIT,
                'unit' => 'GB',
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],

            // FEATURE (boolean)
            [
                'code' => 'api_access',
                'slug' => 'api-access',
                'name' => 'API Erişimi',
                'description' => 'API erişimi aktif/pasif',
                'type' => FeatureType::FEATURE,
                'unit' => null,
                // ✅ NOT NULL: default veriyoruz
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],
            [
                'code' => 'priority_support',
                'slug' => 'priority-support',
                'name' => 'Öncelikli Destek',
                'description' => 'Öncelikli destek hakkı',
                'type' => FeatureType::FEATURE,
                'unit' => null,
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],

            // METERED + ResetPeriod
            [
                'code' => 'api_requests',
                'slug' => 'api-requests',
                'name' => 'API İstek Limiti',
                'description' => 'Aylık API istek sayısı',
                'type' => FeatureType::METERED,
                'unit' => 'request',
                'reset_period' => ResetPeriod::Monthly,
                'is_active' => true,
            ],
            [
                'code' => 'email_sends',
                'slug' => 'email-sends',
                'name' => 'E-posta Gönderim Limiti',
                'description' => 'Günlük e-posta gönderim sayısı',
                'type' => FeatureType::METERED,
                'unit' => 'adet',
                'reset_period' => ResetPeriod::Daily,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Feature::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
