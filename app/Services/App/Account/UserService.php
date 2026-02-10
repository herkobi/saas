<?php

/**
 * User Management Service
 *
 * Handles tenant user management operations including listing,
 * role changes, user removal, and activity tracking.
 *
 * @package    App\Services\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Contracts\App\Account\UserServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Enums\TenantUserRole;
use App\Enums\UserStatus;
use App\Events\TenantUserRemoved;
use App\Events\TenantUserRoleChanged;
use App\Events\TenantUserStatusChanged;
use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service for managing tenant users.
 */
class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    /**
     * Get paginated users for a tenant.
     */
    public function getPaginated(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return $tenant->users()
                ->withPivot(['role', 'status', 'joined_at'])
                ->wherePivot('role', TenantUserRole::OWNER->value)
                ->orderBy('tenant_user.joined_at', 'desc')
                ->paginate($perPage);
        }

        $query = $tenant->users()
            ->withPivot(['role', 'status', 'joined_at']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->wherePivot('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortField = $filters['sort'] ?? 'tenant_user.joined_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage);
    }

    /**
     * Get all users for a tenant.
     */
    public function getAll(Tenant $tenant): Collection
    {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return $tenant->users()
                ->withPivot(['role', 'status', 'joined_at'])
                ->wherePivot('role', TenantUserRole::OWNER->value)
                ->orderBy('tenant_user.joined_at', 'desc')
                ->get();
        }

        return $tenant->users()
            ->withPivot(['role', 'status', 'joined_at'])
            ->orderBy('tenant_user.joined_at', 'desc')
            ->get();
    }

    /**
     * Find a tenant user by ID.
     */
    public function findById(Tenant $tenant, string $userId): ?User
    {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return $tenant->users()
                ->withPivot(['role', 'status', 'joined_at'])
                ->wherePivot('role', TenantUserRole::OWNER->value)
                ->where('users.id', $userId)
                ->first();
        }

        return $tenant->users()
            ->withPivot(['role', 'status', 'joined_at'])
            ->where('users.id', $userId)
            ->first();
    }

    /**
     * Change a tenant user's role.
     */
    public function changeRole(
        Tenant $tenant,
        User $targetUser,
        int $newRole,
        User $changedBy,
        string $ipAddress,
        string $userAgent
    ): void {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return;
        }

        $oldRole = $tenant->users()
            ->where('users.id', $targetUser->id)
            ->first()
            ?->pivot
            ?->role;

        $tenant->users()->updateExistingPivot($targetUser->id, [
            'role' => $newRole,
        ]);

        TenantUserRoleChanged::dispatch(
            $tenant,
            $targetUser,
            $oldRole,
            $newRole,
            $changedBy,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Remove a user from a tenant.
     */
    public function removeUser(
        Tenant $tenant,
        User $targetUser,
        User $removedBy,
        string $ipAddress,
        string $userAgent
    ): void {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return;
        }

        $role = $tenant->users()
            ->where('users.id', $targetUser->id)
            ->first()
            ?->pivot
            ?->role;

        $tenant->users()->detach($targetUser->id);

        TenantUserRemoved::dispatch(
            $tenant,
            $targetUser,
            $role,
            $removedBy,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Get user activity logs for a tenant.
     */
    public function getUserActivities(Tenant $tenant, User $targetUser, int $perPage = 15): LengthAwarePaginator
    {
        if (!$this->tenantContextService->teamMembersAllowed()) {
            return new LengthAwarePaginator(collect(), 0, $perPage);
        }

        return Activity::where('tenant_id', $tenant->id)
            ->where('user_id', $targetUser->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if the current user can manage the target user.
     */
    public function canManageUser(User $manager, User $targetUser, Tenant $tenant): bool
    {
        $targetRole = $tenant->users()
            ->where('users.id', $targetUser->id)
            ->first()
            ?->pivot
            ?->role;

        $managerRole = $tenant->users()
            ->where('users.id', $manager->id)
            ->first()
            ?->pivot
            ?->role;

        if (!$targetRole || !$managerRole) {
            return false;
        }

        if ((int) $targetRole === TenantUserRole::OWNER->value) {
            return false;
        }

        return (int) $managerRole === TenantUserRole::OWNER->value;
    }

    /**
     * Check if the current user can change the target user's role.
     */
    public function canChangeRole(User $manager, User $targetUser, Tenant $tenant, int $newRole): bool
    {
        if (!$this->canManageUser($manager, $targetUser, $tenant)) {
            return false;
        }

        if ($newRole === TenantUserRole::OWNER->value) {
            return false;
        }

        return true;
    }

    /**
     * Get the tenant owner.
     */
    public function getOwner(Tenant $tenant): ?User
    {
        return $tenant->users()
            ->wherePivot('role', TenantUserRole::OWNER->value)
            ->first();
    }

    /**
     * Get the number of users for a tenant.
     */
    public function getUserCount(Tenant $tenant): int
    {
        return $tenant->users()->count();
    }

    /**
     * Check if a tenant can invite a team member.
     */
    public function canInviteTeamMember(Tenant $tenant): bool
    {
        return $this->tenantContextService->canInviteTeamMember($tenant);
    }

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
    ): void {
        if (! $this->tenantContextService->teamMembersAllowed()) {
            return;
        }

        $pivotStatus = $tenant->users()
            ->where('users.id', $targetUser->id)
            ->first()
            ?->pivot
            ?->status;

        $oldStatus = $pivotStatus !== null
            ? UserStatus::from((int) $pivotStatus)
            : UserStatus::ACTIVE;

        $tenant->users()->updateExistingPivot($targetUser->id, [
            'status' => $newStatus->value,
        ]);

        TenantUserStatusChanged::dispatch(
            $tenant,
            $targetUser,
            $oldStatus,
            $newStatus,
            $reason,
            $changedBy,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Check if a user can change another user's status within a tenant.
     */
    public function canChangeStatus(User $manager, User $targetUser, Tenant $tenant): bool
    {
        if ($manager->id === $targetUser->id) {
            return false;
        }

        return $this->canManageUser($manager, $targetUser, $tenant);
    }
}
