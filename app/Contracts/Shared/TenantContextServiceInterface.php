<?php

declare(strict_types=1);

namespace App\Contracts\Shared;

use App\Models\Tenant;
use App\Models\User;

interface TenantContextServiceInterface
{
    public function teamMembersAllowed(): bool;

    public function multipleTenantsAllowed(): bool;

    public function canCreateNewTenant(User $user): bool;

    public function canInviteTeamMember(Tenant $tenant): bool;

    public function tenantSessionKey(): string;

    public function setCurrentTenant(?Tenant $tenant): void;

    public function currentTenant(): ?Tenant;

    public function forgetCurrentTenant(): void;
}
