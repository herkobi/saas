<?php

/**
 * Panel Tenant Service
 *
 * This service handles tenant management operations for the panel,
 * including listing, filtering, updating, and statistics retrieval.
 *
 * @package    App\Services\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Tenant;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantUserRole;
use App\Events\PanelTenantUpdated;
use App\Models\Activity;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service for managing tenants from the panel.
 *
 * Provides methods for tenant CRUD operations, activity retrieval,
 * user management, and statistics with comprehensive audit logging.
 */
class TenantService
{
    /**
     * Get paginated list of tenants with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated tenant results
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Tenant::with(['subscription.price.plan', 'users']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['plan_id'])) {
            $query->whereHas('subscription.price', function ($q) use ($filters) {
                $q->where('plan_id', $filters['plan_id']);
            });
        }

        if (!empty($filters['subscription_status'])) {
            $status = SubscriptionStatus::tryFrom($filters['subscription_status']);
            if ($status) {
                $query->whereHas('subscription', function ($q) use ($status) {
                    $this->applySubscriptionStatusFilter($q, $status);
                });
            }
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage)
            ->through(function ($tenant) {
                $owner = $tenant->users->first(fn ($u) => (int) $u->pivot->role === TenantUserRole::OWNER->value);
                $subscription = $tenant->subscription;

                return array_merge($tenant->toArray(), [
                    'owner_name' => $owner?->name,
                    'owner_email' => $owner?->email,
                    'plan_name' => $subscription?->price?->plan?->name,
                    'subscription_status' => $subscription?->status?->value,
                    'subscription_status_label' => $subscription?->status?->label(),
                    'subscription_status_badge' => $subscription?->status?->badge(),
                ]);
            })
            ->withQueryString();
    }

    /**
     * Get all tenants without pagination.
     *
     * @return Collection Collection of all tenants
     */
    public function getAll(): Collection
    {
        return Tenant::with(['subscription.price.plan'])->get();
    }

    /**
     * Get all active tenants without pagination.
     *
     * Active tenants are those with valid subscriptions (ACTIVE, TRIALING, CANCELED, or PAST_DUE).
     *
     * @return Collection Collection of active tenants
     */
    public function getActive(): Collection
    {
        return Tenant::active()
            ->with(['subscription.price.plan'])
            ->orderBy('code')
            ->get();
    }

    /**
     * Find a tenant by its ID.
     *
     * @param string $id The tenant ULID
     * @return Tenant|null The tenant or null if not found
     */
    public function findById(string $id): ?Tenant
    {
        return Tenant::with([
            'subscription.price.plan',
            'users',
            'featureOverrides.feature',
        ])->find($id);
    }

    /**
     * Find a tenant by its unique code.
     *
     * @param string $code The tenant code
     * @return Tenant|null The tenant or null if not found
     */
    public function findByCode(string $code): ?Tenant
    {
        return Tenant::with([
            'subscription.price.plan',
            'users',
            'featureOverrides.feature',
        ])->where('code', $code)->first();
    }

    /**
     * Update a tenant's information.
     *
     * @param Tenant $tenant The tenant to update
     * @param array<string, mixed> $data The data to update
     * @param User $admin The admin user performing the action
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Tenant The updated tenant instance
     */
    public function update(
        Tenant $tenant,
        array $data,
        User $admin,
        string $ipAddress,
        string $userAgent
    ): Tenant {
        $originalData = $tenant->toArray();

        $tenant->update($data);

        $changes = $this->getChanges($originalData, $tenant->toArray());

        PanelTenantUpdated::dispatch(
            $tenant,
            $admin,
            $changes,
            $ipAddress,
            $userAgent
        );

        return $tenant->fresh();
    }

    /**
     * Get paginated activities for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get activities for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated activity results
     */
    public function getActivities(Tenant $tenant, int $perPage = 15): LengthAwarePaginator
    {
        return Activity::where('tenant_id', $tenant->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all users belonging to a tenant.
     *
     * @param Tenant $tenant The tenant to get users for
     * @return Collection Collection of tenant users
     */
    public function getUsers(Tenant $tenant): Collection
    {
        return $tenant->users()->withPivot(['role', 'status', 'joined_at'])->get();
    }

    /**
     * Get paginated payments for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get payments for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated payment results
     */
    public function getPayments(Tenant $tenant, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::withoutTenantScope()->where('tenant_id', $tenant->id)
            ->with('subscription.price.plan')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn ($payment) => array_merge($payment->toArray(), [
                'status_label' => $payment->status->label(),
                'status_badge' => $payment->status->badge(),
            ]))
            ->withQueryString();
    }

    /**
     * Get statistics for a specific tenant.
     *
     * @param Tenant $tenant The tenant to get statistics for
     * @return array<string, mixed> Array of statistics data
     */
    public function getStatistics(Tenant $tenant): array
    {
        $totalUsers = $tenant->users()->count();

        $totalPayments = Payment::withoutTenantScope()->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::COMPLETED)
            ->count();

        $totalRevenue = Payment::withoutTenantScope()->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::COMPLETED)
            ->sum('amount');

        $lastPayment = Payment::withoutTenantScope()->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::COMPLETED)
            ->latest('paid_at')
            ->first();

        $subscription = $tenant->subscription;

        return [
            'total_users' => $totalUsers,
            'total_payments' => $totalPayments,
            'total_revenue' => (float) $totalRevenue,
            'last_payment_at' => $lastPayment?->paid_at,
            'subscription_status' => $subscription?->status?->value,
            'current_plan' => $subscription?->price?->plan?->name,
            'subscription_ends_at' => $subscription?->ends_at,
            'created_at' => $tenant->created_at,
        ];
    }

    /**
     * Get index-level statistics for all tenants.
     *
     * @return array<string, mixed>
     */
    public function getIndexStatistics(): array
    {
        $totalTenants = Tenant::count();

        $withActiveSubscription = Tenant::whereHas('subscription', function ($q) {
            $q->where('status', SubscriptionStatus::ACTIVE);
        })->count();

        $withTrialing = Tenant::whereHas('subscription', function ($q) {
            $q->where('status', SubscriptionStatus::TRIALING);
        })->count();

        $withExpired = Tenant::whereHas('subscription', function ($q) {
            $q->where('status', SubscriptionStatus::EXPIRED);
        })->count();

        return [
            'total_count' => $totalTenants,
            'active_count' => $withActiveSubscription,
            'trialing_count' => $withTrialing,
            'expired_count' => $withExpired,
        ];
    }

    public function getRecentActivities(int $limit = 15): Collection
    {
        return Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Apply subscription status filter to query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     * @param SubscriptionStatus $status The status to filter by
     * @return void
     */
    private function applySubscriptionStatusFilter($query, SubscriptionStatus $status): void
    {
        $now = now();

        match ($status) {
            SubscriptionStatus::TRIALING => $query->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', $now),

            SubscriptionStatus::ACTIVE => $query->where(function ($q) use ($now) {
                $q->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '<=', $now);
            })
                ->whereNull('canceled_at')
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $now);
                }),

            SubscriptionStatus::CANCELED => $query->whereNotNull('canceled_at')
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $now);
                }),

            SubscriptionStatus::PAST_DUE => $query->whereNotNull('ends_at')
                ->where('ends_at', '<', $now)
                ->whereNotNull('grace_period_ends_at')
                ->where('grace_period_ends_at', '>', $now),

            SubscriptionStatus::EXPIRED => $query->whereNotNull('ends_at')
                ->where('ends_at', '<', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('grace_period_ends_at')
                        ->orWhere('grace_period_ends_at', '<=', $now);
                }),
        };
    }

    /**
     * Get changes between original and updated data.
     *
     * @param array<string, mixed> $original Original data
     * @param array<string, mixed> $updated Updated data
     * @return array<string, array{old: mixed, new: mixed}> Array of changes
     */
    private function getChanges(array $original, array $updated): array
    {
        $changes = [];
        $trackFields = ['code', 'slug', 'domain', 'account', 'data', 'status'];

        foreach ($trackFields as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $updated[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
