<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Contracts\Shared\TenantContextServiceInterface;
use App\Models\Tenant;
use App\Models\User;

class TenantContextService implements TenantContextServiceInterface
{
    protected ?Tenant $currentTenant = null;

    public function tenantSessionKey(): string
    {
        return 'tenant_id';
    }

    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        if ($tenant) {
            session()->put($this->tenantSessionKey(), $tenant->id);
        } else {
            session()->forget($this->tenantSessionKey());
        }

        app()->instance(Tenant::class, $tenant);
    }

    public function currentTenant(): ?Tenant
    {
        if ($this->currentTenant) {
            return $this->currentTenant;
        }

        $tenantId = session($this->tenantSessionKey());

        if (! $tenantId) {
            return null;
        }

        return Tenant::find($tenantId);
    }

    public function forgetCurrentTenant(): void
    {
        $this->currentTenant = null;
        session()->forget($this->tenantSessionKey());
        app()->forgetInstance(Tenant::class);
    }

    public function teamMembersAllowed(): bool
    {
        return (bool) config('herkobi.tenant.allow_team_members', false);
    }

    public function multipleTenantsAllowed(): bool
    {
        return (bool) config('herkobi.tenant.allow_multiple_tenants', false);
    }

    public function canCreateNewTenant(User $user): bool
    {
        if (! $this->multipleTenantsAllowed()) {
            return ! $user->tenants()->exists();
        }

        return true;
    }

    public function canInviteTeamMember(Tenant $tenant): bool
    {
        if (! $this->teamMembersAllowed()) {
            return false;
        }

        /** @var \App\Contracts\App\Account\FeatureUsageServiceInterface $featureUsageService */
        $featureUsageService = app(\App\Contracts\App\Account\FeatureUsageServiceInterface::class);
        $limitCheck = $featureUsageService->checkFeatureLimit($tenant, 'users');

        return $limitCheck['allowed'];
    }
}
