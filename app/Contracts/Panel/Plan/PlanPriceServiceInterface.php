<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Plan;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\User;
use Illuminate\Support\Collection;

interface PlanPriceServiceInterface
{
    public function getByPlan(Plan $plan): Collection;
    public function findById(string $id): ?PlanPrice;
    public function create(Plan $plan, array $data, User $user, string $ipAddress, string $userAgent): PlanPrice;
    public function update(PlanPrice $price, array $data, User $user, string $ipAddress, string $userAgent): PlanPrice;
    public function delete(PlanPrice $price, User $user, string $ipAddress, string $userAgent): void;
    public function getMatrixByPlan(Plan $plan): array;
}
