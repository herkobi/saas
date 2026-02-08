<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Subscription;
use App\Models\Tenant;

interface SubscriptionServiceInterface
{
    public function getCurrentSubscription(Tenant $tenant): ?Subscription;

    public function getSubscriptionDetails(Tenant $tenant): array;

    public function getPlanFeatures(Tenant $tenant): array;

    public function getAvailablePlans(): array;

    public function canUpgrade(Tenant $tenant): bool;

    public function canDowngrade(Tenant $tenant): bool;

    public function getDaysRemaining(Tenant $tenant): ?int;

    public function isOnTrial(Tenant $tenant): bool;

    public function isOnGracePeriod(Tenant $tenant): bool;
}
