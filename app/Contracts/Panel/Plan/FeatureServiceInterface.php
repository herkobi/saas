<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Plan;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FeatureServiceInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getAll(): Collection;
    public function getActive(): Collection;
    public function findById(string $id): ?Feature;
    public function findBySlug(string $slug): ?Feature;
    public function create(array $data, User $user, string $ipAddress, string $userAgent): Feature;
    public function update(Feature $feature, array $data, User $user, string $ipAddress, string $userAgent): Feature;
    public function delete(Feature $feature, User $user, string $ipAddress, string $userAgent): void;
}
