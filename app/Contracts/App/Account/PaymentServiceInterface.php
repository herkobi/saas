<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentServiceInterface
{
    public function getPaginated(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(Tenant $tenant, string $id): ?Payment;

    public function getStatistics(Tenant $tenant): array;

    public function getLastPayment(Tenant $tenant): ?Payment;

    public function getPendingPayments(Tenant $tenant): int;

    public function getRecentPayments(Tenant $tenant, int $limit = 5): array;

    public function getDashboardStatistics(Tenant $tenant): array;
}
