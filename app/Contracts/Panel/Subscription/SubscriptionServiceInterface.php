<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Subscription;

use App\Models\Subscription;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubscriptionServiceInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): ?Subscription;

    public function getPayments(Subscription $subscription, int $perPage = 15): LengthAwarePaginator;

    public function getStatistics(array $filters = []): array; // Sadece bu isim kaldı.

    public function getRevenueByPlan(?string $startDate = null, ?string $endDate = null): Collection;

    public function getPlanDistribution(): Collection;

    public function getExpiringSubscriptions(int $days = 7, int $limit = 10): Collection;
}
