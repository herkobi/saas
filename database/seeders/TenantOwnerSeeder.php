<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
use App\Enums\PaymentStatus;
use App\Enums\PlanInterval;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantUserRole;
use App\Enums\UserType;
use App\Models\Checkout;
use App\Models\Feature;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class TenantOwnerSeeder extends Seeder
{
    private int $invoiceCounter = 100;

    public function run(): void
    {
        DB::transaction(function () {
            $items = [
                // normal aktifler
                ['email' => 'tenant1@tenant.com',  'name' => 'Tenant Owner 1',  'title' => 'Demo Company 1',  'plan_slug' => 'free',       'scenario' => 'active'],
                ['email' => 'tenant2@tenant.com',  'name' => 'Tenant Owner 2',  'title' => 'Demo Company 2',  'plan_slug' => 'standard',   'scenario' => 'active'],
                ['email' => 'tenant3@tenant.com',  'name' => 'Tenant Owner 3',  'title' => 'Demo Company 3',  'plan_slug' => 'pro',        'scenario' => 'active'],
                ['email' => 'tenant4@tenant.com',  'name' => 'Tenant Owner 4',  'title' => 'Demo Company 4',  'plan_slug' => 'business',   'scenario' => 'active'],
                ['email' => 'tenant5@tenant.com',  'name' => 'Tenant Owner 5',  'title' => 'Demo Company 5',  'plan_slug' => 'enterprise', 'scenario' => 'active'],
                ['email' => 'tenant6@tenant.com',  'name' => 'Tenant Owner 6',  'title' => 'Demo Company 6',  'plan_slug' => 'standard',   'scenario' => 'active'],

                // trial
                ['email' => 'tenant7@tenant.com',  'name' => 'Tenant Owner 7',  'title' => 'Trial Company 1', 'plan_slug' => 'pro',        'scenario' => 'trial'],
                ['email' => 'tenant8@tenant.com',  'name' => 'Tenant Owner 8',  'title' => 'Trial Company 2', 'plan_slug' => 'business',   'scenario' => 'trial'],

                // grace (PAST_DUE ama erişim devam)
                ['email' => 'tenant9@tenant.com',  'name' => 'Tenant Owner 9',  'title' => 'Grace Company',   'plan_slug' => 'standard',   'scenario' => 'grace'],

                // grace bitti -> EXPIRED
                ['email' => 'tenant10@tenant.com', 'name' => 'Tenant Owner 10', 'title' => 'Overdue Company', 'plan_slug' => 'standard',   'scenario' => 'expired'],

                // canceled (dönem bitmemiş)
                ['email' => 'tenant11@tenant.com', 'name' => 'Tenant Owner 11', 'title' => 'Canceled Co',     'plan_slug' => 'pro',        'scenario' => 'canceled'],

                // tenant’a özel plan
                ['email' => 'tenant12@tenant.com', 'name' => 'Tenant Owner 12', 'title' => 'Private Plan Co', 'plan_slug' => null,         'scenario' => 'tenant_private'],
            ];

            foreach ($items as $item) {
                $user = User::updateOrCreate(
                    ['email' => $item['email']],
                    [
                        'name' => $item['name'],
                        'password' => Hash::make('password'),
                        'user_type' => UserType::TENANT,
                        'email_verified_at' => now(),
                    ]
                );

                if ($user->tenants()->exists()) {
                    continue;
                }

                $code = $this->uniqueTenantCode();

                $tenant = Tenant::create([
                    'code' => $code,
                    'slug' => Str::slug($item['title'].' '.$code),
                    'status' => SubscriptionStatus::ACTIVE,
                    'account' => ['title' => $item['title']],
                ]);

                $tenant->users()->attach($user->id, [
                    'role' => TenantUserRole::OWNER->value,
                    'joined_at' => now()->subDays(rand(10, 60)),
                ]);

                // PlanPrice seçimi: public plan ya da tenant private plan
                if ($item['scenario'] === 'tenant_private') {
                    $planPrice = $this->createTenantPrivatePlanPrice($tenant);
                } else {
                    $plan = Plan::where('slug', $item['plan_slug'])->first();
                    $planPrice = $plan?->prices()->first();
                }

                if (! $planPrice) {
                    continue;
                }

                $subscription = $this->createSubscriptionForScenario($tenant, $planPrice, (string) $item['scenario']);

                // Tenant status = subscription status (UI demo)
                $tenant->update(['status' => $subscription->status]);

                $plan = $planPrice->plan;

                // Free plan: ödeme yok
                if ($plan->is_free) {
                    continue;
                }

                // İlk ödeme: completed (geçmişte)
                $this->createPaymentWithCheckout(
                    tenant: $tenant,
                    subscription: $subscription,
                    planPrice: $planPrice,
                    paymentStatus: PaymentStatus::COMPLETED,
                    checkoutStatus: CheckoutStatus::COMPLETED,
                    at: $subscription->starts_at->copy()->addHours(2),
                    description: 'İlk abonelik ödemesi',
                    checkoutType: CheckoutType::NEW->value
                );

                // Yenileme denemeleri (past_due / expired)
                if ($subscription->status === SubscriptionStatus::PAST_DUE) {
                    $this->createPaymentWithCheckout($tenant, $subscription, $planPrice, PaymentStatus::FAILED,  CheckoutStatus::EXPIRED,  now()->subDays(2),  'Yenileme (başarısız)', CheckoutType::RENEW->value);
                    $this->createPaymentWithCheckout($tenant, $subscription, $planPrice, PaymentStatus::PENDING, CheckoutStatus::PENDING,  now()->subHours(6), 'Yenileme (beklemede)', CheckoutType::RENEW->value, false);
                }

                if ($subscription->status === SubscriptionStatus::EXPIRED) {
                    $this->createPaymentWithCheckout($tenant, $subscription, $planPrice, PaymentStatus::FAILED, CheckoutStatus::FAILED,   now()->subDays(14), 'Yenileme (başarısız)', CheckoutType::RENEW->value);
                    $this->createPaymentWithCheckout($tenant, $subscription, $planPrice, PaymentStatus::FAILED, CheckoutStatus::EXPIRED,  now()->subDays(9),  'Yenileme (süresi doldu)', CheckoutType::RENEW->value);
                }
            }
        });
    }

    private function uniqueTenantCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (Tenant::where('code', $code)->exists());

        return $code;
    }

    private function createTenantPrivatePlanPrice(Tenant $tenant): PlanPrice
    {
        $plan = Plan::create([
            'slug' => 'private-'.$tenant->code,
            'name' => ($tenant->account['title'] ?? 'Tenant').' Özel Plan',
            'description' => 'Tenant özel plan (demo)',
            'tenant_id' => $tenant->id,
            'is_free' => false,
            'is_active' => true,
            'is_public' => false,
            'grace_period_days' => 10,
            'grace_period_policy' => \App\Enums\GracePeriod::RESTRICTED,
        ]);

        $price = PlanPrice::create([
            'plan_id' => $plan->id,
            'currency' => 'TRY',
            'interval' => PlanInterval::MONTH,
            'interval_count' => 1,
            'price' => '777.00',
            'trial_days' => 0,
        ]);

        $features = Feature::query()->pluck('id', 'code');

        $map = [
            ($features['users'] ?? null) => '12',
            ($features['storage_gb'] ?? null) => '150',
            ($features['api_access'] ?? null) => '1',
            ($features['priority_support'] ?? null) => '1',
            ($features['api_requests'] ?? null) => '75000',
            ($features['email_sends'] ?? null) => '1500',
        ];

        $rows = [];
        foreach ($map as $featureId => $value) {
            if (! $featureId) continue;
            $rows[$featureId] = ['value' => $value];
        }

        $plan->features()->sync($rows);

        return $price;
    }

    private function createSubscriptionForScenario(Tenant $tenant, PlanPrice $planPrice, string $scenario): Subscription
    {
        $startsAt = now()->subDays(30);
        $endsAt = now()->addDays(335);
        $trialEndsAt = null;
        $canceledAt = null;
        $graceEndsAt = null;

        if ($scenario === 'trial') {
            $startsAt = now()->subDays(3);
            $trialEndsAt = now()->addDays(7);
            $endsAt = now()->addDays(335);
        } elseif ($scenario === 'grace') {
            $startsAt = now()->subDays(60);
            $endsAt = now()->subDays(2);
            $graceEndsAt = now()->addDays(5);
        } elseif ($scenario === 'expired') {
            $startsAt = now()->subDays(90);
            $endsAt = now()->subDays(20);
            $graceEndsAt = now()->subDays(10);
        } elseif ($scenario === 'canceled') {
            $startsAt = now()->subDays(40);
            $endsAt = now()->addDays(20);
            $canceledAt = now()->subDays(7);
        }

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $planPrice->id,
            'status' => SubscriptionStatus::ACTIVE, // sonra updateStatus hesaplayacak
            'starts_at' => $startsAt,
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => $planPrice->plan->is_free ? null : $endsAt,
            'canceled_at' => $canceledAt,
            'grace_period_ends_at' => $graceEndsAt,
        ]);

        $subscription->updateStatus();

        // Trialing’i zorla (calculateStatus zaten trial_ends_at ile TRIALING döndürür)
        if ($scenario === 'trial') {
            $subscription->update(['status' => SubscriptionStatus::TRIALING]);
        }

        // canceled senaryosu
        if ($scenario === 'canceled') {
            $subscription->update(['status' => SubscriptionStatus::CANCELED]);
        }

        return $subscription->fresh();
    }

    private function createPaymentWithCheckout(
        Tenant $tenant,
        Subscription $subscription,
        PlanPrice $planPrice,
        PaymentStatus $paymentStatus,
        CheckoutStatus $checkoutStatus,
        \Carbon\CarbonInterface $at,
        string $description,
        string $checkoutType,
        bool $finalState = true
    ): void {
        $oid = 'OID-'.Str::upper(Str::random(12));

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'gateway' => 'paytr',
            'gateway_payment_id' => 'PAYTR-'.Str::random(16),
            'amount' => $planPrice->price,
            'currency' => $planPrice->currency,
            'status' => $paymentStatus->value,
            'description' => $description,
            'gateway_response' => ['status' => $paymentStatus->value, 'merchant_oid' => $oid],
            'metadata' => ['type' => 'subscription', 'merchant_oid' => $oid],
            'paid_at' => $paymentStatus === PaymentStatus::COMPLETED ? $at : null,
            'invoiced_at' => $paymentStatus === PaymentStatus::COMPLETED ? $at : null,
            'invoice_number' => $paymentStatus === PaymentStatus::COMPLETED ? 'INV-'.str_pad((string) $this->invoiceCounter++, 6, '0', STR_PAD_LEFT) : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        Checkout::create([
            'tenant_id' => $tenant->id,
            'plan_price_id' => $planPrice->id,
            'payment_id' => $payment->id,
            'merchant_oid' => $oid,
            'type' => $checkoutType,
            'status' => $checkoutStatus->value,
            'amount' => $planPrice->price,
            'proration_credit' => '0.00',
            'final_amount' => $planPrice->price,
            'currency' => $planPrice->currency,
            'paytr_token' => Str::random(64),
            'billing_info' => [
                'name' => $tenant->account['title'] ?? 'Demo Company',
                'email' => $tenant->owner()?->email ?? 'demo@example.com',
            ],
            'metadata' => ['type' => 'subscription', 'final' => $finalState],
            'expires_at' => $at->copy()->addHour(),
            'completed_at' => $checkoutStatus === CheckoutStatus::COMPLETED ? $at : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
