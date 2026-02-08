<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Addon;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantAddon;
use Illuminate\Database\Eloquent\Collection;

interface AddonPurchaseServiceInterface
{
    public function getAvailableAddons(Tenant $tenant): Collection;

    public function purchaseAddon(Tenant $tenant, Addon $addon, int $quantity): Checkout;

    public function calculateAddonPrice(Addon $addon, int $quantity): array;

    public function processCheckout(Checkout $checkout, Payment $payment): TenantAddon;
}
