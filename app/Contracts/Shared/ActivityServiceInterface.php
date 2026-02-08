<?php

/**
 * Activity Service Interface Contract
 *
 * This interface defines the contract for panel activity service
 * implementations, providing methods for activity logging and retrieval
 * within the panel domain for comprehensive activity tracking.
 *
 * @package    App\Contracts\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Shared;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for panel activity service implementations.
 *
 * This interface defines the contract for managing activities
 * in the general panel context, including logging and retrieval operations.
 */
interface ActivityServiceInterface
{

    /**
     * Log user-specific activity with full context.
     *
     * @param User $user The user performing the action
     * @param string $type The type of activity being logged
     * @param string $description Human-readable description of the activity
     * @param array $log Additional context data for the activity
     * @param string|null $tenantId The tenant ID if activity is tenant-scoped
     * @return void
     */
    public function log(User $user, string $type, string $description, array $log, ?string $tenantId = null): void;

    /**
     * Log an anonymous or system-level activity.
     *
     * @param string $type The type of activity being logged
     * @param string $description Human-readable description of the activity
     * @param array $log Additional context data for the activity
     * @return void
     */
    public function logAnonymousActivity(string $type, string $description, array $log): void;

    /**
     * Get paginated activities with sorting.
     *
     * @param array $filters The sorting/filtering parameters to apply.
     * @param int $perPage Number of activities per page.
     * @return LengthAwarePaginator Paginated activity results.
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all unique activity types.
     *
     * @param array $filters Filtreleme parametreleri
     * @return array<string, string> An associative array of activity type keys and names.
     */
    public function getActivityTypes(array $filters = []): array;
}
