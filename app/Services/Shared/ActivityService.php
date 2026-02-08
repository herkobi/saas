<?php

/**
 * Activity Service
 *
 * This service handles all panel activity-related operations including logging,
 * retrieval, and cache management for user activities.
 *
 * @package    App\Services\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Shared;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\ActivityLogged;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Activity Service
 *
 * Service implementation for managing panel user activities including
 * activity logging, retrieval, and cache management for the panel context.
 */
class ActivityService implements ActivityServiceInterface
{
    /**
     * Log user-specific activity with full context.
     *
     * @param User $user
     * @param string $type
     * @param string $description
     * @param array<string, mixed> $log
     * @param string|null $tenantId
     * @return void
     */
    public function log(User $user, string $type, string $description, array $log, ?string $tenantId = null): void
    {
        $activity = Activity::create([
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'tenant_id' => $tenantId,
            'type' => $type,
            'description' => $description,
            'log' => $log,
        ]);

        ActivityLogged::dispatch(
            $user,
            $activity,
            $log['ip_address'] ?? '127.0.0.1',
            $log['user_agent'] ?? 'unknown',
            $log
        );
    }

    /**
     * Log an anonymous or system-level activity.
     *
     * @param string $type
     * @param string $description
     * @param array $log
     * @return void
     */
    public function logAnonymousActivity(string $type, string $description, array $log): void
    {
        Activity::create([
            'user_id' => null,
            'user_type' => null,
            'tenant_id' => null,
            'type' => $type,
            'description' => $description,
            'log' => $log,
        ]);
    }

    /**
     * Get paginated activities with sorting.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::query()->with('user');

        if (!empty($filters['user_type_group'])) {
            if ($filters['user_type_group'] === 'platform_admin') {
                $query->platformAdmin();
            } elseif ($filters['user_type_group'] === 'tenant') {
                $query->tenant();
            }
        }

        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Get all unique activity types.
     *
     * @param array $filters
     * @return array<string, string>
     */
    public function getActivityTypes(array $filters = []): array
    {
        $query = Activity::query();

        if (!empty($filters['user_type_group'])) {
            if ($filters['user_type_group'] === 'platform_admin') {
                $query->platformAdmin();
            } elseif ($filters['user_type_group'] === 'tenant') {
                $query->tenant();
            }
        }

        return $query
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type', 'type')
            ->toArray();
    }
}
