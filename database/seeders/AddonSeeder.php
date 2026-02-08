<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlanInterval;
use App\Models\Addon;
use App\Models\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $storage = Feature::where('code', 'storage_gb')->first();
        $users = Feature::where('code', 'users')->first();
        $priority = Feature::where('code', 'priority_support')->first();

        if (! $storage || ! $users || ! $priority) {
            return;
        }

        $items = [
            [
                'name' => 'Ek Depolama +50GB',
                'slug' => 'extra-storage-50gb',
                'description' => 'Depolamayı 50 GB artırır',
                'feature_id' => $storage->id,
                'addon_type' => 'increment',
                'value' => '50',
                'price' => '150.00',
                'currency' => 'TRY',
                'is_recurring' => true,
                'interval' => PlanInterval::MONTH,
                'interval_count' => 1,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Ek Kullanıcı +10',
                'slug' => 'extra-users-10',
                'description' => 'Kullanıcı limitini 10 artırır',
                'feature_id' => $users->id,
                'addon_type' => 'increment',
                'value' => '10',
                'price' => '100.00',
                'currency' => 'TRY',
                'is_recurring' => true,
                'interval' => PlanInterval::MONTH,
                'interval_count' => 1,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Öncelikli Destek',
                'slug' => 'priority-support',
                'description' => 'Öncelikli destek aktif olur',
                'feature_id' => $priority->id,
                'addon_type' => 'boolean',
                'value' => '1',
                'price' => '250.00',
                'currency' => 'TRY',
                'is_recurring' => true,
                'interval' => PlanInterval::MONTH,
                'interval_count' => 1,
                'is_active' => true,
                'is_public' => true,
            ],
        ];

        foreach ($items as $item) {
            Addon::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
