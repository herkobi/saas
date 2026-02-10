<?php

declare(strict_types=1);

namespace App\Services\App;

use App\Contracts\App\TenantServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantUserRole;
use App\Events\TenantRegistered;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    public function getOwnedTenants(User $user): Collection
    {
        return $user->tenants()
            ->wherePivot('role', TenantUserRole::OWNER->value)
            ->orderByPivot('joined_at')
            ->get();
    }

    public function getAllTenants(User $user): Collection
    {
        return $user->tenants()
            ->withPivot(['role', 'status', 'joined_at'])
            ->orderByPivot('joined_at')
            ->get();
    }

    public function canCreate(User $user): bool
    {
        return $this->tenantContextService->canCreateNewTenant($user);
    }

    public function create(User $user, array $data, string $ipAddress, string $userAgent): Tenant
    {
        if (! $this->canCreate($user)) {
            throw new \InvalidArgumentException(
                'Yeni tenant oluşturma izniniz yok veya tenant limitine ulaştınız.'
            );
        }

        return DB::transaction(function () use ($user, $data, $ipAddress, $userAgent) {
            $code = $this->generateUniqueTenantCode();

            $tenant = Tenant::create([
                'code' => $code,
                'slug' => Str::slug($data['name'] . '-' . $code),
                'status' => SubscriptionStatus::ACTIVE,
                'account' => [
                    'title' => $data['name'],
                ],
            ]);

            $tenant->users()->attach($user->id, [
                'role' => TenantUserRole::OWNER->value,
                'joined_at' => now(),
            ]);

            TenantRegistered::dispatch($user, $tenant, $ipAddress, $userAgent);

            return $tenant;
        });
    }

    public function switchTenant(User $user, string $tenantId): ?Tenant
    {
        $tenant = $user->tenants()
            ->where('tenants.id', $tenantId)
            ->first();

        if (! $tenant) {
            return null;
        }

        $this->tenantContextService->setCurrentTenant($tenant);

        return $tenant;
    }

    private function generateUniqueTenantCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (Tenant::where('code', $code)->exists());

        return $code;
    }
}
