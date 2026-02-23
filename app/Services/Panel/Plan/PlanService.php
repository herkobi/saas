<?php

declare(strict_types=1);

namespace App\Services\Panel\Plan;

use App\Events\PanelPlanArchived;
use App\Events\PanelPlanCreated;
use App\Events\PanelPlanUpdated;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PlanService
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Plan::query()
            ->with(['prices', 'features'])
            ->withCount('subscriptions');

        // archived: '1' => archived, '0' veya null => not archived
        if (!empty($filters['archived']) && $filters['archived'] === '1') {
            $query->whereNotNull('archived_at');
        } else {
            // Default: arşivlenmemişleri göster
            $query->whereNull('archived_at');
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getAll(): Collection
    {
        return Plan::query()
            ->with(['prices', 'features'])
            ->orderBy('name')
            ->get();
    }

    public function getActive(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->with(['prices', 'features'])
            ->orderBy('name')
            ->get();
    }

    public function getPublic(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereNull('archived_at')
            ->with(['prices', 'features'])
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?Plan
    {
        return Plan::query()
            ->with(['prices', 'features'])
            ->find($id);
    }

    public function findOrFailById(string $id): Plan
    {
        return Plan::query()
            ->with(['prices', 'features'])
            ->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Plan
    {
        return Plan::query()
            ->with(['prices', 'features'])
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data, User $user, string $ipAddress, string $userAgent): Plan
    {
        // Yeni planlar her zaman pasif oluşturulur
        $data['is_active'] = false;

        // Public ise tenant_id null
        if (!empty($data['is_public'])) {
            $data['tenant_id'] = null;
        }

        $plan = Plan::query()->create($data);

        PanelPlanCreated::dispatch($plan, $user, $ipAddress, $userAgent);

        return $plan;
    }

    public function update(Plan $plan, array $data, User $user, string $ipAddress, string $userAgent): Plan
    {
        $oldData = $plan->toArray();

        // Public ise tenant_id null
        if (array_key_exists('is_public', $data) && !empty($data['is_public'])) {
            $data['tenant_id'] = null;
        }

        $plan->update($data);

        PanelPlanUpdated::dispatch($plan, $user, $oldData, $ipAddress, $userAgent);

        return $plan->fresh();
    }

    public function publish(Plan $plan, User $user, string $ipAddress, string $userAgent): void
    {
        $oldData = $plan->toArray();

        $plan->update([
            'is_active' => true,
        ]);

        PanelPlanUpdated::dispatch($plan, $user, $oldData, $ipAddress, $userAgent);
    }

    public function unpublish(Plan $plan, User $user, string $ipAddress, string $userAgent): void
    {
        $oldData = $plan->toArray();

        $plan->update([
            'is_active' => false,
        ]);

        PanelPlanUpdated::dispatch($plan, $user, $oldData, $ipAddress, $userAgent);
    }

    public function archive(Plan $plan, User $user, string $ipAddress, string $userAgent): void
    {
        // Arşive alınan plan pasifleşsin
        $plan->update([
            'archived_at' => now(),
            'is_active' => false,
        ]);

        PanelPlanArchived::dispatch($plan, $user, $ipAddress, $userAgent);
    }

    public function restore(Plan $plan, User $user, string $ipAddress, string $userAgent): void
    {
        $plan->update([
            'archived_at' => null,
        ]);

        PanelPlanUpdated::dispatch($plan, $user, [], $ipAddress, $userAgent);
    }

    public function syncFeatures(Plan $plan, array $features, User $user, string $ipAddress, string $userAgent): void
    {
        $syncData = [];

        foreach ($features as $feature) {
            if (empty($feature['feature_id'])) {
                continue;
            }

            $value = $feature['value'] ?? null;

            // -1 = unlimited (null olarak sakla)
            if ($value === '-1' || $value === -1) {
                $value = null;
            }

            // Boş string = unlimited (null olarak sakla)
            if ($value === '') {
                $value = null;
            }

            $syncData[$feature['feature_id']] = [
                'value' => $value,
            ];
        }

        $plan->features()->sync($syncData);

        PanelPlanUpdated::dispatch($plan, $user, [], $ipAddress, $userAgent);
    }

    public function getTenantsUsingPlan(Plan $plan): Collection
    {
        return Tenant::query()
            ->with(['subscription.price']) // Tenantlar tabı için (type + expires)
            ->whereHas('subscription.price', function ($q) use ($plan) {
                $q->where('plan_id', $plan->id);
            })
            ->orderBy('code')
            ->get();
    }

    public function getEnabledFeaturesForEdit(Plan $plan): array
    {
        return $plan->features
            ->mapWithKeys(function ($feature) {
                $value = $feature->pivot?->value;

                $featureType = $feature->type?->value ?? (string) $feature->type;

                // Feature tipi (Boolean): null veya boş ise '1' (Var)
                if ($featureType === 'feature' && ($value === null || $value === '')) {
                    $value = '1';
                }

                // Limit veya Metered tipi: null ise '-1' (Sınırsız)
                if (($featureType === 'limit' || $featureType === 'metered') && $value === null) {
                    $value = '-1';
                }

                return [$feature->id => $value];
            })
            ->toArray();
    }

    public function getTenantListForDisplay(Plan $plan): Collection
    {
        $tenants = $this->getTenantsUsingPlan($plan);

        return $tenants->map(function ($tenant) {
            $sub = $tenant->subscription;
            $subPrice = $sub?->price;

            $count = (int) ($subPrice?->interval_count ?? 1);
            $intervalEnum = $subPrice?->interval;
            $intervalValue = $intervalEnum instanceof \App\Enums\PlanInterval
                ? $intervalEnum->value
                : ($intervalEnum !== null ? (string) $intervalEnum : null);

            $intervalLabel = $intervalEnum instanceof \App\Enums\PlanInterval
                ? $intervalEnum->shortLabel()
                : ($intervalValue ? strtoupper($intervalValue) : '—');

            $typeLabel = ($count > 1 && $intervalLabel !== '—')
                ? $count . ' ' . $intervalLabel
                : $intervalLabel;

            $expiresAt = $sub->expires_at ?? $sub->ends_at ?? null;
            $expiresFormatted = $expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt)->format('d.m.Y') : null;

            $daysLeft = null;
            if ($expiresAt) {
                $daysLeft = now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($expiresAt)->startOfDay(), false);
            }

            $badgeClass = null;
            if ($daysLeft !== null) {
                if ($daysLeft < 0) {
                    $badgeClass = 'bg-red-lt';
                } elseif ($daysLeft <= 7) {
                    $badgeClass = 'bg-red-lt';
                } elseif ($daysLeft <= 30) {
                    $badgeClass = 'bg-yellow-lt';
                } else {
                    $badgeClass = 'bg-secondary-lt';
                }
            }

            return [
                'id' => $tenant->id,
                'code' => $tenant->code ?? '—',
                'domain' => $tenant->domain ?? null,
                'type_label' => $typeLabel,
                'expires_formatted' => $expiresFormatted,
                'days_left' => $daysLeft,
                'badge_class' => $badgeClass,
            ];
        });
    }
}
