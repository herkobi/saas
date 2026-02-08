<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Contracts\App\Account\PaymentServiceInterface;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService implements PaymentServiceInterface
{
    public function getPaginated(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::where('tenant_id', $tenant->id)
            ->with(['subscription.price.plan']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage);
    }

    public function findById(Tenant $tenant, string $id): ?Payment
    {
        return Payment::where('tenant_id', $tenant->id)
            ->with(['subscription.price.plan'])
            ->find($id);
    }

    public function getStatistics(Tenant $tenant): array
    {
        $payments = Payment::where('tenant_id', $tenant->id);

        return [
            'total_count' => $payments->count(),
            'total_paid' => (clone $payments)->completed()->sum('amount'),
            'pending_count' => (clone $payments)->pending()->count(),
            'pending_amount' => (clone $payments)->pending()->sum('amount'),
            'last_payment_date' => (clone $payments)->completed()->latest('paid_at')->value('paid_at'),
        ];
    }

    public function getLastPayment(Tenant $tenant): ?Payment
    {
        return Payment::where('tenant_id', $tenant->id)
            ->completed()
            ->latest('paid_at')
            ->first();
    }

    public function getPendingPayments(Tenant $tenant): int
    {
        return Payment::where('tenant_id', $tenant->id)
            ->pending()
            ->count();
    }
}
