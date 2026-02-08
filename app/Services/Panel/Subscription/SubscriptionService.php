<?php

declare(strict_types=1);

namespace App\Services\Panel\Subscription;

use App\Contracts\Panel\Subscription\SubscriptionServiceInterface;
use App\Enums\SubscriptionStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Subscription::withoutTenantScope()
            ->with(['tenant', 'price.plan', 'nextPrice.plan'])
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $query->whereHas('tenant', fn($q) =>
                    $q->where('code', 'like', "%{$filters['search']}%")
                      ->orWhere('name', 'like', "%{$filters['search']}%")
                );
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $this->applyStatusFilter($query, SubscriptionStatus::from($filters['status']));
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findById(string $id): ?Subscription
    {
        return Subscription::withoutTenantScope()
            ->with(['tenant', 'planPrice.plan', 'nextPlanPrice.plan'])
            ->find($id);
    }

    public function getPayments(Subscription $subscription, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::withoutTenantScope()->where('subscription_id', $subscription->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Tek isim, tek sorumluluk.
     */
    public function getStatistics(array $filters = []): array
    {
        $baseQuery = Subscription::withoutTenantScope();

        return [
            'total'      => (clone $baseQuery)->count(),
            'active'     => (clone $baseQuery)->where('status', SubscriptionStatus::ACTIVE)->count(),
            'trialing'   => (clone $baseQuery)->where('status', SubscriptionStatus::TRIALING)->count(),
            'past_due'   => (clone $baseQuery)->where('status', SubscriptionStatus::PAST_DUE)->count(),
            'revenue_30d' => Payment::withoutTenantScope()
                ->where('status', PaymentStatus::COMPLETED)
                ->where('paid_at', '>=', now()->subDays(30))
                ->sum('amount'),
        ];
    }

    public function getRevenueByPlan(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('payments')
            ->join('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
            ->join('plan_prices', 'subscriptions.plan_price_id', '=', 'plan_prices.id')
            ->join('plans', 'plan_prices.plan_id', '=', 'plans.id')
            ->where('payments.status', PaymentStatus::COMPLETED->value)
            ->when($startDate, fn($q) => $q->where('payments.paid_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payments.paid_at', '<=', $endDate))
            ->select('plans.name', DB::raw('SUM(payments.amount) as total_revenue'))
            ->groupBy('plans.name')
            ->get();
    }

    protected function applyStatusFilter($query, SubscriptionStatus $status): void
    {
        $now = now();
        match ($status) {
            SubscriptionStatus::TRIALING => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', $now),
            SubscriptionStatus::ACTIVE => $query->where(fn($q) => $q->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', $now))
                ->whereNull('canceled_at')
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now)),
            SubscriptionStatus::CANCELED => $query->whereNotNull('canceled_at')->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now)),
            SubscriptionStatus::PAST_DUE => $query->whereNotNull('ends_at')->where('ends_at', '<', $now)->whereNotNull('grace_period_ends_at')->where('grace_period_ends_at', '>', $now),
            SubscriptionStatus::EXPIRED => $query->whereNotNull('ends_at')->where('ends_at', '<', $now)->where(fn($q) => $q->whereNull('grace_period_ends_at')->orWhere('grace_period_ends_at', '<', $now)),
        };
    }
}
