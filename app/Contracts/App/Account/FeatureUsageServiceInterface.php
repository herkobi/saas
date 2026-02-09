<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Tenant;
use Illuminate\Support\Collection;

interface FeatureUsageServiceInterface
{
    public function getAllFeatures(Tenant $tenant): Collection;

    public function getFeatureUsage(Tenant $tenant, string $featureSlug): array;

    public function getLimitFeatures(Tenant $tenant): Collection;

    public function getMeteredFeatures(Tenant $tenant): Collection;

    public function getBooleanFeatures(Tenant $tenant): Collection;

    public function checkFeatureAccess(Tenant $tenant, string $featureSlug): bool;

    public function checkFeatureLimit(Tenant $tenant, string $featureSlug): array;

    public function incrementUsage(Tenant $tenant, string $featureSlug, int $amount = 1): bool;

    public function decrementUsage(Tenant $tenant, string $featureSlug, int $amount = 1): bool;

    public function resetUsage(Tenant $tenant, string $featureSlug): bool;

    public function resolveWithReason(Tenant $tenant, string $featureSlug): array;
}
