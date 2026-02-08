<?php

/**
 * Tenant Addon Seeder
 *
 * Seeds tenant addon purchases with associated payments and checkouts.
 * Creates realistic addon purchase scenarios for standard and pro plan tenants.
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

use App\Enums\PaymentStatus;
use App\Models\Addon;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantAddon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TenantAddonSeeder extends Seeder
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

            $storageAddon = Addon::where('slug', 'extra-storage-50gb')->first();
            $usersAddon = Addon::where('slug', 'extra-users-10')->first();
            $priorityAddon = Addon::where('slug', 'priority-support')->first();

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                $plan = $tenant->subscription?->price?->plan;

                if (! $plan) {
                    continue;
                }

                // Free plan addon almaz
                if ($plan->slug === 'free') {
                    continue;
                }

                // Standard plan: Extra storage + users
                if ($plan->slug === 'standard' && $storageAddon && $usersAddon) {
                    $this->createAddonPurchase($tenant, $storageAddon, 12);
                    $this->createAddonPurchase($tenant, $usersAddon, 8);
                }

                // Pro plan: Priority support + extra storage
                if ($plan->slug === 'pro' && $storageAddon && $priorityAddon) {
                    $this->createAddonPurchase($tenant, $priorityAddon, 10);
                    $this->createAddonPurchase($tenant, $storageAddon, 5);
                }
            }
        });
    }

    /**
     * Create addon purchase with payment and checkout
     *
     * @param  \App\Models\Tenant  $tenant
     * @param  \App\Models\Addon  $addon
     * @param  int  $daysAgo
     * @return void
     */
    private function createAddonPurchase(Tenant $tenant, Addon $addon, int $daysAgo): void
    {
        // tenant_addons kaydı
        $tenantAddon = TenantAddon::create([
            'tenant_id' => $tenant->id,
            'addon_id' => $addon->id,
            'quantity' => 1,
            'started_at' => now()->subDays($daysAgo),
            'expires_at' => $addon->is_recurring ? now()->addDays(30 - $daysAgo) : null,
            'is_active' => true,
            'metadata' => [
                'purchase_date' => now()->subDays($daysAgo)->toDateString(),
                'feature_code' => $addon->feature->code ?? null,
            ],
        ]);

        $oid = 'OID-'.Str::upper(Str::random(12));

        // Payment kaydı
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $tenant->subscription?->id,
            'addon_id' => $addon->id,
            'gateway' => 'paytr',
            'gateway_payment_id' => 'PAYTR-'.Str::random(16),
            'amount' => $addon->price,
            'currency' => $addon->currency,
            'status' => PaymentStatus::COMPLETED->value,
            'description' => "Addon satın alımı: {$addon->name}",
            'gateway_response' => ['status' => 'success', 'merchant_oid' => $oid],
            'metadata' => [
                'type' => 'addon',
                'addon_slug' => $addon->slug,
                'merchant_oid' => $oid,
            ],
            'paid_at' => now()->subDays($daysAgo),
            'invoiced_at' => now()->subDays($daysAgo),
        ]);

        // Checkout kaydı
        Checkout::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $tenant->subscription->plan_price_id,
            'addon_id' => $addon->id,
            'quantity' => 1,
            'payment_id' => $payment->id,
            'merchant_oid' => $oid,
            'type' => 'addon',
            'status' => 'completed',
            'amount' => $addon->price,
            'proration_credit' => '0.00',
            'final_amount' => $addon->price,
            'currency' => $addon->currency,
            'paytr_token' => Str::random(64),
            'billing_info' => [
                'name' => $tenant->account['title'] ?? 'Demo Company',
                'email' => $tenant->users()->first()->email ?? 'demo@example.com',
            ],
            'metadata' => [
                'type' => 'addon',
                'addon_name' => $addon->name,
            ],
            'expires_at' => now()->subDays($daysAgo)->addHour(),
            'completed_at' => now()->subDays($daysAgo),
        ]);
    }
}
