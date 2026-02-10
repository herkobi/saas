<?php

/**
 * User Management Service Interface Contract
 *
 * Defines the contract for tenant user management operations
 * including listing, role changes, and user removal.
 *
 * @package    App\Contracts\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Enums\UserStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for tenant user management service implementations.
 */
interface UserServiceInterface
{
    /**
     * Get paginated users for a tenant.
     *
     * @param Tenant $tenant The tenant to get users for
     * @param array $filters Filter parameters (search, role, status, sort, direction)
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator
     */
    public function getPaginated(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all users for a tenant.
     *
     * @param Tenant $tenant The tenant to get users for
     * @return Collection
     */
    public function getAll(Tenant $tenant): Collection;

    /**
     * Find a user by ID within a tenant.
     *
     * @param Tenant $tenant The tenant context
     * @param string $userId The user ID to find
     * @return User|null
     */
    public function findById(Tenant $tenant, string $userId): ?User;

    /**
     * Change a user's role within a tenant.
     *
     * @param Tenant $tenant The tenant context
     * @param User $targetUser The user whose role will change
     * @param int $newRole The new role value
     * @param User $changedBy The user performing the change
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function changeRole(
        Tenant $tenant,
        User $targetUser,
        int $newRole,
        User $changedBy,
        string $ipAddress,
        string $userAgent
    ): void;

    /**
     * Remove a user from a tenant.
     *
     * @param Tenant $tenant The tenant context
     * @param User $targetUser The user to remove
     * @param User $removedBy The user performing the removal
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function removeUser(
        Tenant $tenant,
        User $targetUser,
        User $removedBy,
        string $ipAddress,
        string $userAgent
    ): void;

    /**
     * Get user activities within a tenant.
     *
     * @param Tenant $tenant The tenant context
     * @param User $targetUser The user to get activities for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator
     */
    public function getUserActivities(Tenant $tenant, User $targetUser, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if a user can manage another user.
     *
     * @param User $manager The user attempting to manage
     * @param User $targetUser The user being managed
     * @param Tenant $tenant The tenant context
     * @return bool
     */
    public function canManageUser(User $manager, User $targetUser, Tenant $tenant): bool;

    /**
     * Check if a user can change another user's role.
     *
     * @param User $manager The user attempting the change
     * @param User $targetUser The user whose role would change
     * @param Tenant $tenant The tenant context
     * @param int $newRole The target role
     * @return bool
     */
    public function canChangeRole(User $manager, User $targetUser, Tenant $tenant, int $newRole): bool;

    /**
     * Get the owner of a tenant.
     *
     * @param Tenant $tenant The tenant
     * @return User|null
     */
    public function getOwner(Tenant $tenant): ?User;

    /**
     * Get the total user count for a tenant.
     *
     * @param Tenant $tenant The tenant
     * @return int
     */
    public function getUserCount(Tenant $tenant): int;

    /**
     * Check if team member invitations are allowed for a tenant.
     *
     * @param Tenant $tenant The tenant context
     * @return bool
     */
    public function canInviteTeamMember(Tenant $tenant): bool;

    /**
     * Change a user's status within a tenant (pivot-based).
     */
    public function changeStatus(
        Tenant $tenant,
        User $targetUser,
        UserStatus $newStatus,
        ?string $reason,
        User $changedBy,
        string $ipAddress,
        string $userAgent
    ): void;

    /**
     * Check if a user can change another user's status within a tenant.
     */
    public function canChangeStatus(User $manager, User $targetUser, Tenant $tenant): bool;
}
