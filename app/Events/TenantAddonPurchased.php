<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Tenant;
use App\Models\TenantAddon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantAddonPurchased
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public TenantAddon $tenantAddon,
        public string $ip,
        public string $userAgent
    ) {}
}
