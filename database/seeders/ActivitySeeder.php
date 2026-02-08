<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $faker = fake();

            // PANEL
            $admin = User::where('email', 'admin@admin.com')->first();
            if ($admin) {
                $types = [
                    'panel.plan_created'       => ['Plan oluşturuldu', 'created_at'],
                    'panel.plan_updated'       => ['Plan güncellendi', 'updated_at'],
                    'panel.plan_archived'      => ['Plan arşivlendi', 'archived_at'],
                    'panel.plan_price_created' => ['Plan fiyatı oluşturuldu', 'created_at'],
                    'panel.plan_price_updated' => ['Plan fiyatı güncellendi', 'updated_at'],
                    'panel.plan_price_deleted' => ['Plan fiyatı silindi', 'deleted_at'],
                ];

                for ($i = 0; $i < rand(25, 60); $i++) {
                    $at = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440));
                    [$type, $desc, $stampKey] = $this->pick($types);

                    $ip = $faker->ipv4();
                    $ua = $faker->userAgent();

                    Activity::create([
                        'user_id' => $admin->id,
                        'user_type' => $admin->user_type,
                        'tenant_id' => null,
                        'type' => $type,
                        'description' => $desc,
                        'log' => array_filter([
                            'ip_address' => $ip,
                            'user_agent' => $ua,
                            $stampKey => $at->toDateTimeString(),
                        ]),
                        'ip_address' => $ip,
                        'user_agent' => $ua,
                        'created_at' => $at,
                        'updated_at' => $at,
                    ]);
                }
            }

            // TENANT
            $tenants = Tenant::with('users')->get();

            $types = [
                'tenant.auth_login' => ['Sisteme giriş yaptı', 'created_at'],
                'tenant.dashboard_view' => ['Dashboard görüntülendi', 'created_at'],
                'tenant.subscription_view' => ['Abonelik sayfası görüntülendi', 'created_at'],
                'tenant.billing_view' => ['Faturalandırma sayfası görüntülendi', 'created_at'],
                'tenant.subscription_renewal_attempt' => ['Abonelik yenileme denemesi yapıldı', 'created_at'],
                'tenant.payment_failed' => ['Ödeme başarısız oldu', 'created_at'],
                'tenant.feature_limit_warning' => ['Kullanım limiti uyarısı alındı', 'created_at'],
                'tenant.feature_limit_reached' => ['Kullanım limiti aşıldı', 'created_at'],
            ];

            foreach ($tenants as $tenant) {
                if ($tenant->users->isEmpty()) continue;

                $events = rand(40, 120);

                for ($i = 0; $i < $events; $i++) {
                    $user = $tenant->users->random();
                    $at = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440));

                    $status = $tenant->status?->value ?? null;

                    // gecikmiş/expired -> ödeme eventleri daha sık
                    if (in_array($status, ['past_due', 'expired'], true) && rand(1, 100) <= 45) {
                        $type = rand(1, 100) <= 55 ? 'tenant.payment_failed' : 'tenant.subscription_renewal_attempt';
                        [$desc, $stampKey] = $types[$type];
                    } else {
                        [$type, $desc, $stampKey] = $this->pick($types);
                    }

                    $ip = $faker->ipv4();
                    $ua = $faker->userAgent();

                    $featureCode = $faker->randomElement(['users','storage_gb','api_requests','email_sends']);

                    Activity::create([
                        'user_id' => $user->id,
                        'user_type' => $user->user_type,
                        'tenant_id' => $tenant->id,
                        'type' => $type,
                        'description' => $desc,
                        'log' => array_filter([
                            'tenant_code' => $tenant->code,
                            'tenant_title' => $tenant->account['title'] ?? null,
                            'ip_address' => $ip,
                            'user_agent' => $ua,
                            $stampKey => $at->toDateTimeString(),
                            'feature_code' => in_array($type, ['tenant.feature_limit_warning','tenant.feature_limit_reached'], true)
                                ? $featureCode
                                : null,
                        ]),
                        'ip_address' => $ip,
                        'user_agent' => $ua,
                        'created_at' => $at,
                        'updated_at' => $at,
                    ]);
                }
            }
        });
    }

    /**
     * @param array<string, array{0:string,1:string}> $map
     * @return array{0:string,1:string,2:string}
     */
    private function pick(array $map): array
    {
        $keys = array_keys($map);
        $type = $keys[array_rand($keys)];
        [$desc, $stampKey] = $map[$type];

        return [$type, $desc, $stampKey];
    }
}
