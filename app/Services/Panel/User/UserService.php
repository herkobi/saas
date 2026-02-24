<?php

declare(strict_types=1);

namespace App\Services\Panel\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Get paginated list of users with optional filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['tenants']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
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
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status->value,
                    'status_label' => $user->status->label(),
                    'status_badge' => $user->status->badge(),
                    'user_type' => $user->user_type->value,
                    'user_type_label' => $user->user_type->label(),
                    'tenants' => $user->tenants->map(fn ($tenant) => [
                        'id' => $tenant->id,
                        'name' => $tenant->name ?? $tenant->code,
                        'role' => $tenant->pivot->role,
                    ]),
                    'last_login_at' => $user->last_login_at?->toISOString(),
                    'created_at' => $user->created_at->toISOString(),
                ];
            })
            ->withQueryString();
    }

    /**
     * Get user statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => User::count(),
            'active_count' => User::where('status', UserStatus::ACTIVE)->count(),
            'passive_count' => User::where('status', UserStatus::PASSIVE)->count(),
            'draft_count' => User::where('status', UserStatus::DRAFT)->count(),
            'admin_count' => User::where('user_type', UserType::ADMIN)->count(),
            'tenant_count' => User::where('user_type', UserType::TENANT)->count(),
        ];
    }
}
