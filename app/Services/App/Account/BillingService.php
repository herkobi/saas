<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Contracts\App\Account\BillingServiceInterface;
use App\Events\TenantBillingUpdated;
use App\Models\Tenant;
use App\Models\User;
use App\Helpers\PaymentHelper;

class BillingService implements BillingServiceInterface
{
    public function getAccount(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id,
            'code' => $tenant->code,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'status' => [
                'value' => $tenant->status->value,
                'label' => $tenant->status->label(),
            ],
            'account' => $tenant->account ?? [],
            'created_at' => $tenant->created_at->toISOString(),
        ];
    }

    public function updateAccount(Tenant $tenant, array $data, User $user, string $ipAddress, string $userAgent): Tenant
    {
        $oldData = $tenant->toArray();

        $tenant->update($data);

        TenantBillingUpdated::dispatch($tenant, $user, $oldData, $ipAddress, $userAgent);

        return $tenant->fresh();
    }

    public function getBillingInfo(Tenant $tenant): array
    {
        $account = $tenant->account ?? [];

        return [
            'company_name' => $account['company_name'] ?? null,
            'tax_office' => $account['tax_office'] ?? null,
            'tax_number' => $account['tax_number'] ?? null,
            'address' => $account['address'] ?? null,
            'city' => $account['city'] ?? null,
            'country' => $account['country'] ?? PaymentHelper::country(),
            'postal_code' => $account['postal_code'] ?? null,
            'phone' => $account['phone'] ?? null,
            'billing_email' => $account['billing_email'] ?? null,
        ];
    }

    public function updateBillingInfo(Tenant $tenant, array $data, User $user, string $ipAddress, string $userAgent): Tenant
    {
        $oldData = $tenant->toArray();

        $account = $tenant->account ?? [];
        $account = array_merge($account, $data);

        $tenant->update(['account' => $account]);

        TenantBillingUpdated::dispatch($tenant, $user, $oldData, $ipAddress, $userAgent);

        return $tenant->fresh();
    }
}
