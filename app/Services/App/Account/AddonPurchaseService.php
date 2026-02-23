<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Enums\CheckoutType;
use App\Enums\PlanInterval;
use App\Events\TenantAddonPurchased;
use App\Models\Addon;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Helpers\PaymentHelper;
use App\Helpers\TaxHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddonPurchaseService
{
    public function getAvailableAddons(Tenant $tenant): Collection
    {
        return Addon::with('feature')
            ->active()
            ->public()
            ->get();
    }

    public function purchaseAddon(Tenant $tenant, Addon $addon, int $quantity): Checkout
    {
        $priceCalculation = $this->calculateAddonPrice($addon, $quantity);

        $checkout = Checkout::create([
            'tenant_id' => $tenant->id,
            'addon_id' => $addon->id,
            'type' => CheckoutType::ADDON,
            'quantity' => $quantity,
            'merchant_oid' => $this->generateMerchantOid(),
            'status' => 'pending',
            'amount' => $priceCalculation['final_amount'],
            'currency' => $addon->currency,
            'metadata' => [
                'addon_name' => $addon->name,
                'addon_type' => $addon->addon_type->value,
                'feature_name' => $addon->feature->name,
                'is_recurring' => $addon->is_recurring,
                'quantity' => $quantity,
                'subtotal' => $priceCalculation['subtotal'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            'expires_at' => now()->addMinutes(PaymentHelper::sessionTimeout()),
        ]);

        return $checkout;
    }

    public function calculateAddonPrice(Addon $addon, int $quantity): array
    {
        $unitPrice = (float) $addon->price;
        $subtotal = $unitPrice * $quantity;

        $taxRate = TaxHelper::decimalRate();
        $tax = $subtotal * $taxRate;

        $finalAmount = $subtotal + $tax;

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate * 100,
            'tax' => round($tax, 2),
            'final_amount' => round($finalAmount, 2),
            'currency' => $addon->currency,
        ];
    }

    public function processCheckout(Checkout $checkout, Payment $payment): TenantAddon
    {
        return DB::transaction(function () use ($checkout, $payment) {
            $tenant = $checkout->tenant;
            $addon = $checkout->addon;
            $quantity = $checkout->quantity ?? 1;

            $pivotData = [
                'quantity' => $quantity,
                'started_at' => now(),
                'is_active' => true,
            ];

            if ($addon->is_recurring) {
                $pivotData['expires_at'] = $this->calculateExpiration($addon);
            }

            $existing = $tenant->addons()->where('addon_id', $addon->id)->first();

            if ($existing) {
                $tenant->addons()->updateExistingPivot($addon->id, $pivotData);
            } else {
                $tenant->addons()->attach($addon->id, $pivotData);
            }

            $tenantAddon = $tenant->addons()
                ->where('addon_id', $addon->id)
                ->first()
                ->pivot;

            $payment->update(['addon_id' => $addon->id]);

            $ip = $checkout->metadata['ip'] ?? '0.0.0.0';
            $userAgent = $checkout->metadata['user_agent'] ?? 'system';

            TenantAddonPurchased::dispatch($tenant, $tenantAddon, $ip, $userAgent);

            return $tenantAddon;
        });
    }

    protected function calculateExpiration(Addon $addon): \Carbon\Carbon
    {
        $count = $addon->interval_count ?? 1;

        return match ($addon->interval) {
            PlanInterval::DAY => now()->addDays($count),
            PlanInterval::MONTH => now()->addMonths($count),
            PlanInterval::YEAR => now()->addYears($count),
            default => now()->addMonth(),
        };
    }

    protected function generateMerchantOid(): string
    {
        return 'ADDON-' . Str::ulid();
    }
}
