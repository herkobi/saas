<?php

declare(strict_types=1);

namespace App\Services\Panel\Plan;

use App\Contracts\Panel\Plan\FeatureServiceInterface;
use App\Events\PanelFeatureCreated;
use App\Events\PanelFeatureDeleted;
use App\Events\PanelFeatureUpdated;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeatureService implements FeatureServiceInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Feature::query();

        if (!empty($filters['search'])) {
            $search = (string) $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) (int) $filters['is_active']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Sıralama alanlarını whitelist yap
        $allowedSorts = ['created_at', 'name', 'code', 'type', 'is_active'];
        $sortField = (string) ($filters['sort'] ?? 'created_at');
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'created_at';
        }

        $sortDirection = strtolower((string) ($filters['direction'] ?? 'desc'));
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        // PerPage sınırı
        $perPage = max(1, min(100, (int) $perPage));

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage)
            ->through(fn ($feature) => array_merge($feature->toArray(), [
                'type_label' => $feature->type->label(),
            ]));
    }

    public function getAll(): Collection
    {
        return Feature::orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return Feature::active()->orderBy('name')->get();
    }

    public function findById(string $id): ?Feature
    {
        return Feature::find($id);
    }

    public function findBySlug(string $slug): ?Feature
    {
        return Feature::where('slug', $slug)->first();
    }

    public function create(array $data, User $user, string $ipAddress, string $userAgent): Feature
    {
        $data['slug'] = $data['slug'] ?? Str::slug((string) $data['name']);

        $feature = Feature::create($data);

        PanelFeatureCreated::dispatch($feature, $user, $ipAddress, $userAgent);

        return $feature;
    }

    public function update(Feature $feature, array $data, User $user, string $ipAddress, string $userAgent): Feature
    {
        $oldData = $feature->toArray();

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        $feature->update($data);

        PanelFeatureUpdated::dispatch($feature, $user, $oldData, $ipAddress, $userAgent);

        return $feature->fresh();
    }

    public function delete(Feature $feature, User $user, string $ipAddress, string $userAgent): void
    {
        PanelFeatureDeleted::dispatch($feature, $user, $ipAddress, $userAgent);

        $feature->delete();
    }
}
