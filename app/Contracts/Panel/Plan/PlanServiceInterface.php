<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlanServiceInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getAll(): Collection;
    public function getActive(): Collection;
    public function getPublic(): Collection;
    public function findById(string $id): ?Plan;
    public function findOrFailById(string $id): Plan;
    public function findBySlug(string $slug): ?Plan;
    public function create(array $data, User $user, string $ipAddress, string $userAgent): Plan;
    public function update(Plan $plan, array $data, User $user, string $ipAddress, string $userAgent): Plan;
    public function publish(Plan $plan, User $user, string $ipAddress, string $userAgent): void;
    public function unpublish(Plan $plan, User $user, string $ipAddress, string $userAgent): void;
    public function archive(Plan $plan, User $user, string $ipAddress, string $userAgent): void;
    public function restore(Plan $plan, User $user, string $ipAddress, string $userAgent): void;
    public function syncFeatures(Plan $plan, array $features, User $user, string $ipAddress, string $userAgent): void;
    public function getTenantsUsingPlan(Plan $plan): Collection;
    public function getEnabledFeaturesForEdit(Plan $plan): array;
    public function getTenantListForDisplay(Plan $plan): Collection;
}
