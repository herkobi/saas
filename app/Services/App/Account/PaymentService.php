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

    public function getRecentPayments(Tenant $tenant, int $limit = 5): array
    {
        return $tenant->payments()
            ->with(['subscription.price.plan', 'addon'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => $payment->subscription_id ? 'subscription' : 'addon',
                    'type_label' => $payment->subscription_id ? 'Abonelik' : 'Eklenti',
                    'description' => $payment->subscription_id
                        ? $payment->subscription->price->plan->name
                        : ($payment->addon->name ?? 'Ödeme'),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'status_label' => $payment->status->label(),
                    'status_badge' => $payment->status->badge(),
                    'created_at' => $payment->created_at,
                ];
            })
            ->toArray();
    }

    public function getDashboardStatistics(Tenant $tenant): array
    {
        return [
            'total_payments' => $tenant->payments()->completed()->count(),
            'total_amount' => $tenant->payments()->completed()->sum('amount'),
            'team_members' => $tenant->users()->count(),
        ];
    }
}
