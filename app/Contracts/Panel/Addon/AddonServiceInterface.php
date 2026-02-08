<?php

declare(strict_types=1);

namespace App\Contracts\Panel\Addon;

use App\Models\Addon;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AddonServiceInterface
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function getAll(): Collection;

    public function getActive(): Collection;

    public function getPublic(): Collection;

    public function findById(string $id): ?Addon;

    public function create(array $data, User $user, string $ip, string $userAgent): Addon;

    public function update(Addon $addon, array $data, User $user, string $ip, string $userAgent): Addon;

    public function delete(Addon $addon, User $user, string $ip, string $userAgent): bool;
}
