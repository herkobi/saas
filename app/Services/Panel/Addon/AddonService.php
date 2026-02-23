<?php

declare(strict_types=1);

namespace App\Services\Panel\Addon;

use App\Events\PanelAddonCreated;
use App\Events\PanelAddonDeleted;
use App\Events\PanelAddonUpdated;
use App\Models\Addon;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AddonService
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Addon::with('feature');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('slug', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['feature_id'])) {
            $query->where('feature_id', $filters['feature_id']);
        }

        if (isset($filters['addon_type'])) {
            $query->where('addon_type', $filters['addon_type']);
        }

        if (isset($filters['is_recurring'])) {
            $query->where('is_recurring', $filters['is_recurring']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        return $query->paginate($perPage)
            ->through(fn ($addon) => array_merge($addon->toArray(), [
                'addon_type_label' => $addon->addon_type->label(),
            ]));
    }

    public function getAll(): Collection
    {
        return Addon::with('feature')->get();
    }

    public function getActive(): Collection
    {
        return Addon::with('feature')->active()->get();
    }

    public function getPublic(): Collection
    {
        return Addon::with('feature')->active()->public()->get();
    }

    public function findById(string $id): ?Addon
    {
        return Addon::with('feature')->find($id);
    }

    public function create(array $data, User $user, string $ip, string $userAgent): Addon
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $addon = Addon::create($data);

        event(new PanelAddonCreated($addon, $user, $ip, $userAgent));

        return $addon->load('feature');
    }

    public function update(Addon $addon, array $data, User $user, string $ip, string $userAgent): Addon
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $addon->update($data);

        event(new PanelAddonUpdated($addon, $user, $ip, $userAgent));

        return $addon->fresh('feature');
    }

    public function delete(Addon $addon, User $user, string $ip, string $userAgent): bool
    {
        $deleted = $addon->delete();

        if ($deleted) {
            event(new PanelAddonDeleted($addon, $user, $ip, $userAgent));
        }

        return (bool) $deleted;
    }
}
