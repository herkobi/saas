<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Shared\TenantContextService;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;

trait HasTenantContext
{
    protected function currentTenant(): ?Tenant
    {
        return app(TenantContextService::class)->currentTenant();
    }

    protected function requireTenant(): Tenant|RedirectResponse
    {
        $tenant = $this->currentTenant();

        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        return redirect()->route('register');
    }
}
