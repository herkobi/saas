<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CheckoutType;
use App\Enums\PaymentStatus;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tenants = Tenant::with('subscription.price.plan')->get();

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                $subscription = $tenant->subscription;

                if (! $subscription || ! $subscription->price) {
                    continue;
                }

                $plan = $subscription->price->plan;

                // Free plan için recurring payment yok
                if ($plan->is_free) {
                    continue;
                }

                $planPrice = $subscription->price;

                // 2. aylık ödeme (completed + invoiced)
                $this->createRecurringPayment($tenant, $subscription, $planPrice, 10, PaymentStatus::COMPLETED, true);

                // 3. aylık ödeme (completed, invoice henüz oluşturulmamış)
                $this->createRecurringPayment($tenant, $subscription, $planPrice, 5, PaymentStatus::COMPLETED, false);

                // 4. aylık ödeme (pending)
                $this->createRecurringPayment($tenant, $subscription, $planPrice, 2, PaymentStatus::PENDING, false);
            }
        });
    }

    private function createRecurringPayment(Tenant $tenant, Subscription $subscription, PlanPrice $planPrice, int $daysAgo, PaymentStatus $status, bool $invoiced): void
    {
        $oid = 'OID-'.Str::upper(Str::random(12));

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'gateway' => 'paytr',
            'gateway_payment_id' => $status === PaymentStatus::COMPLETED ? 'PAYTR-'.Str::random(16) : null,
            'amount' => $planPrice->price,
            'currency' => $planPrice->currency,
            'status' => $status->value,
            'description' => 'Aylık abonelik yenileme',
            'gateway_response' => $status === PaymentStatus::COMPLETED ? ['status' => 'success', 'merchant_oid' => $oid] : null,
            'metadata' => ['type' => CheckoutType::RENEW->value, 'merchant_oid' => $oid],
            'paid_at' => $status === PaymentStatus::COMPLETED ? now()->subDays($daysAgo) : null,
            'invoiced_at' => ($status === PaymentStatus::COMPLETED && $invoiced) ? now()->subDays($daysAgo) : null,
        ]);

        Checkout::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $planPrice->id,
            'payment_id' => $payment->id,
            'merchant_oid' => $oid,
            'type' => CheckoutType::RENEW->value,
            'status' => $status === PaymentStatus::COMPLETED ? 'completed' : 'pending',
            'amount' => $planPrice->price,
            'proration_credit' => '0.00',
            'final_amount' => $planPrice->price,
            'currency' => $planPrice->currency,
            'paytr_token' => $status === PaymentStatus::COMPLETED ? Str::random(64) : null,
            'billing_info' => [
                'name' => $tenant->account['title'] ?? 'Demo Company',
                'email' => $tenant->users()->first()->email ?? 'demo@example.com',
            ],
            'metadata' => ['type' => CheckoutType::RENEW->value,],
            'expires_at' => now()->subDays($daysAgo)->addHour(),
            'completed_at' => $status === PaymentStatus::COMPLETED ? now()->subDays($daysAgo) : null,
        ]);
    }
}
